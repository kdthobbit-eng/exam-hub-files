<?php
/* Telegram Mini App: the Super TET mock Admin Panel inside Telegram, for the bot's owner and admins.
   Who is asking is proved by Telegram's signed initData (checked with the bot token already saved on the site). */
if (!defined('ABSPATH')) exit;

function eh_tgapp_url() { return add_query_arg('eh_tgadmin', '1', home_url('/')); }

/* Validate Telegram WebApp initData; returns the Telegram user (array) if the user is a bot owner/admin. */
function eh_tgapp_user() {
    static $u = false; if ($u !== false) return $u; $u = null;
    $tok = function_exists('eh_tgb_token') ? eh_tgb_token() : ''; if ($tok === '') return null;
    $raw = (string) ($_SERVER['HTTP_X_TG_INIT'] ?? ''); if ($raw === '') return null;
    parse_str($raw, $f); $hash = (string) ($f['hash'] ?? ''); unset($f['hash']); if ($hash === '') return null;
    ksort($f); $lines = []; foreach ($f as $k => $v) $lines[] = $k . '=' . $v;
    $secret = hash_hmac('sha256', $tok, 'WebAppData', true);
    if (!hash_equals(hash_hmac('sha256', implode("\n", $lines), $secret), $hash)) return null;
    if (time() - (int) ($f['auth_date'] ?? 0) > DAY_IN_SECONDS) return null;
    $usr = json_decode((string) ($f['user'] ?? ''), true); if (empty($usr['id'])) return null;
    if (!function_exists('eh_tgb_role') || !eh_tgb_role($usr['id'])) return null;
    return $u = $usr;
}

/* Send a file to a Telegram chat (multipart sendDocument). */
function eh_tgapp_send_doc($chat, $name, $bytes, $caption = '') {
    $tok = eh_tgb_token(); if ($tok === '') return false;
    $b = '----eh' . wp_generate_password(16, false); $body = '';
    foreach (['chat_id' => (string) $chat, 'caption' => $caption, 'parse_mode' => 'HTML'] as $k => $v)
        $body .= "--$b\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";
    $body .= "--$b\r\nContent-Disposition: form-data; name=\"document\"; filename=\"$name\"\r\nContent-Type: text/csv\r\n\r\n$bytes\r\n--$b--\r\n";
    $r = wp_remote_post('https://api.telegram.org/bot' . $tok . '/sendDocument', ['timeout' => 30, 'headers' => ['Content-Type' => 'multipart/form-data; boundary=' . $b], 'body' => $body]);
    if (is_wp_error($r)) return false;
    $j = json_decode(wp_remote_retrieve_body($r), true); return !empty($j['ok']);
}

add_action('rest_api_init', function () {
    $ok = function () { return (bool) eh_tgapp_user(); };
    register_rest_route('eh/v1', '/tgapp/report', ['methods' => 'GET', 'permission_callback' => $ok, 'callback' => function (WP_REST_Request $q) {
        $d = eh_stm_report_data(sanitize_key($q['round'] ?? ''));
        $d['rounds'] = array_values(array_filter($d['rounds'], function ($x) { return $x['id'][0] === 'm' || ($x['kind'] ?? '') === 'practice' || in_array($x['series'] ?? '', ['tgt', 'pgt'], true); }));
        if (function_exists('eh_tp_ready')) { $rd = []; foreach (eh_tp_rounds_all() as $x) $rd[$x['id']] = eh_tp_ready($x); foreach ($d['rounds'] as &$x) if (isset($rd[$x['id']])) $x['ready'] = $rd[$x['id']]; unset($x); }
        $d['cur'] = eh_stm_round()['id'];
        if (function_exists('eh_stm_mail_counts')) { $d['mail'] = eh_stm_mail_counts(); $d['mail']['on'] = (int) eh_stm_mail_opt()['on']; }
        $d['me'] = eh_tgapp_user()['first_name'] ?? '';
        $rm = get_option('eh_stm_removed', []); $d['removed'] = [];
        foreach ((array) ($rm[$d['round']['id']] ?? []) as $uid => $x) $d['removed'][] = ['uid' => (int) $uid, 'name' => $x['name'], 'email' => $x['email'], 'score' => $x['score'], 'by' => $x['by'], 'at' => $x['at']];
        global $wpdb; $r = eh_stm_round($d['round']['id']); $now = time();
        $td = wp_date('Y-m-d'); $off = (int) round((float) get_option('gmt_offset') * HOUR_IN_SECONDS);
        $since = function ($ts) use ($wpdb, $off) { return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= %s", gmdate('Y-m-d H:i:s', $ts - $off))); };
        $d0 = strtotime($td . ' 00:00:00 UTC') ;
        $d['signups'] = ['total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}"), 'today' => $since($d0), 'week' => $since($d0 - 6 * DAY_IN_SECONDS),
            'mock' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= %s", gmdate('Y-m-d H:i:s', $r['login_ts'] ?? $r['start_ts'])))];
        $w = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . eh_stm_table() . ' WHERE round = %s AND started > 0 AND submitted = 0 AND last > %d ORDER BY started ASC', $r['id'], $now - 120), ARRAY_A);
        $d['writing'] = array_map(function ($x) use ($r, $now) { return ['name' => $x['name'], 'email' => $x['email'], 'roll' => eh_stm_roll((int) $x['uid']), 'att' => (int) $x['att'], 'answered' => (int) $x['answered'],
            'left' => max(0, eh_stm_eff($r, $x, $now) - $now), 'tabs' => (int) $x['tabs'], 'dev' => $x['dev']]; }, $w);
        return $d;
    }]);
    /* Remove a candidate from the merit list (reversible): their counted attempt is set to not counted and remembered. */
    register_rest_route('eh/v1', '/tgapp/remove', ['methods' => 'POST', 'permission_callback' => $ok, 'callback' => function (WP_REST_Request $q) {
        global $wpdb; $t = eh_stm_table(); $p = $q->get_json_params() ?: [];
        $r = eh_stm_round(sanitize_key($p['round'] ?? '')); $uid = (int) ($p['uid'] ?? 0); if (!$uid) return ['ok' => false];
        $rm = get_option('eh_stm_removed', []); if (!is_array($rm)) $rm = [];
        if (!empty($p['undo'])) {
            $id = (int) ($rm[$r['id']][$uid]['id'] ?? 0);
            if ($id) $wpdb->update($t, ['counted' => 1], ['id' => $id]);
            unset($rm[$r['id']][$uid]);
        } else {
            $row = $wpdb->get_row($wpdb->prepare("SELECT id, name, email, score FROM $t WHERE round = %s AND uid = %d AND counted = 1 AND submitted > 0 ORDER BY att DESC LIMIT 1", $r['id'], $uid), ARRAY_A);
            if (!$row) return ['ok' => false];
            $wpdb->update($t, ['counted' => 0], ['id' => $row['id']]);
            $rm[$r['id']][$uid] = ['id' => (int) $row['id'], 'name' => $row['name'], 'email' => $row['email'], 'score' => (int) $row['score'], 'by' => eh_tgapp_user()['first_name'] ?? '', 'at' => time()];
        }
        update_option('eh_stm_removed', $rm, false);
        return ['ok' => true];
    }]);
    register_rest_route('eh/v1', '/tgapp/top', ['methods' => 'POST', 'permission_callback' => $ok, 'callback' => function (WP_REST_Request $q) {
        $u = eh_tgapp_user(); $p = $q->get_json_params() ?: [];
        $d = eh_stm_report_data(sanitize_key($p['round'] ?? '')); $s = $d['stats'];
        $md = ['🥇', '🥈', '🥉']; $t = '🏆 <b>' . esc_html($d['round']['name']) . " — टॉप 10</b>\n\n";
        foreach (array_slice($d['rows'], 0, 10) as $i => $x) $t .= ($md[$i] ?? ($x['rank'] . '.')) . ' ' . esc_html($x['name']) . ' — <b>' . $x['score'] . '</b>/360' . "\n";
        $t .= "\n👥 मेरिट में: <b>{$s['merit']}</b> उम्मीदवार · औसत: <b>{$s['avg']}</b>\n📝 मॉक दें / रिज़ल्ट देखें: " . home_url('/super-tet-mock/') . "\n\nUPSSSC ALERT — भरोसे का नाम, चाहे Content हो या Update!";
        $r = eh_tgb_api('sendMessage', ['chat_id' => $u['id'], 'text' => $t, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true]);
        return ['ok' => !empty($r['ok'])];
    }]);
    register_rest_route('eh/v1', '/tgapp/send', ['methods' => 'POST', 'permission_callback' => $ok, 'callback' => function (WP_REST_Request $q) {
        $u = eh_tgapp_user(); $p = $q->get_json_params() ?: [];
        $d = eh_stm_report_data(sanitize_key($p['round'] ?? '')); $mask = !empty($p['masked']); $top = max(0, (int) ($p['top'] ?? 0));
        $rows = $top ? array_slice($d['rows'], 0, $top) : $d['rows'];
        $out = fopen('php://temp', 'r+'); fwrite($out, "\xEF\xBB\xBF");
        $h = ['रैंक', 'परसेंटाइल', 'नाम', 'रोल नं.']; if (!$mask) $h[] = 'ईमेल';
        fputcsv($out, array_merge($h, ['अंक (360)', 'सही', 'गलत', 'समय (मिनट)', 'प्रयास']));
        foreach ($rows as $x) { $l = [$x['rank'], $x['pct'], $x['name'], $x['roll']]; if (!$mask) $l[] = $x['email']; fputcsv($out, array_merge($l, [$x['score'], $x['c'], $x['w'], $x['min'], $x['att']])); }
        rewind($out); $csv = stream_get_contents($out); fclose($out);
        $s = $d['stats']; $nm = 'Merit-' . $d['round']['id'] . ($mask ? '-masked' : '') . '-' . wp_date('d-M-H.i') . '.csv';
        $cap = '🏆 <b>' . esc_html($d['round']['name']) . "</b> — मेरिट लिस्ट\n" . ($mask ? 'Masked (ईमेल छिपे)' : 'Unmasked (ईमेल सहित)') . ($top ? " · टॉप $top" : '') .
            "\nमेरिट में: <b>{$s['merit']}</b> · औसत: {$s['avg']} · टॉप: {$s['max']}/360\n" . wp_date('d M Y, h:i A') . "\n\nUPSSSC ALERT · xlstudy.in";
        return ['ok' => eh_tgapp_send_doc($u['id'], $nm, $csv, $cap)];
    }]);
});

/* The Mini App page itself. */
add_action('template_redirect', function () {
    if (empty($_GET['eh_tgadmin'])) return;
    nocache_headers(); header('Content-Type: text/html; charset=utf-8');
    $rest = esc_url_raw(rest_url('eh/v1/tgapp/')); $site = esc_url(home_url('/super-tet-mock/?admin=1'));
    ?><!doctype html>
<html lang="hi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Admin Panel — Super TET Mock</title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Mukta:wght@500;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}body{margin:0;font-family:Mukta,system-ui,sans-serif;background:#eef2f8;color:#0f2a4d;-webkit-tap-highlight-color:transparent}
.hd{background:linear-gradient(125deg,#071534,#123a86 60%,#1d74dc);color:#fff;padding:16px 16px 18px;border-bottom:4px solid #f5b53f}
.hd .t{display:flex;align-items:center;gap:10px}.hd .lg{width:42px;height:42px;border-radius:50%;background:#fff;box-shadow:0 0 0 3px #f5b53f;display:flex;align-items:center;justify-content:center;overflow:hidden}
.hd .lg img{width:100%}.hd b{font-size:19px;letter-spacing:.3px}.hd b em{font-style:normal;color:#ffd277}.hd small{display:block;opacity:.85;font-size:13px}
.rs{display:flex;gap:8px;margin-top:12px;overflow-x:auto}.rs button{flex:none;border:1.5px solid rgba(255,255,255,.4);background:rgba(255,255,255,.08);color:#fff;border-radius:12px;padding:6px 12px;font:inherit;font-size:14px;line-height:1.2;text-align:left}
.rs{flex-wrap:wrap}.rs .sr{display:flex;width:100%;gap:6px;background:rgba(255,255,255,.1);border-radius:12px;padding:4px}.rs .sr button{flex:1;text-align:center;border:0;font-weight:800}.rs select{width:100%;border:0;border-radius:12px;padding:10px 12px;font:inherit;font-size:15px;font-weight:700;color:#0f2a4d}
.rs button.on{background:#f5b53f;border-color:#f5b53f;color:#2a1e00}.rs button small{display:block;font-size:11px;opacity:.85}
.st{display:inline-flex;align-items:center;gap:6px;margin-top:10px;font-size:13px;background:rgba(255,255,255,.12);border-radius:20px;padding:3px 10px}.st i{width:8px;height:8px;border-radius:50%;background:#22c55e}
.wrap{padding:12px}.k{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.k div{background:#fff;border-radius:14px;padding:10px 8px;text-align:center;box-shadow:0 2px 8px rgba(15,42,77,.06)}.k b{display:block;font-size:22px;line-height:1.1}.k span{font-size:12px;color:#5a6b8c}
.k .g b{color:#16a34a}.k .o b{color:#c2410c}
.tb{display:flex;gap:6px;margin:14px 0 10px;background:#dfe6f2;border-radius:14px;padding:4px}.tb button{flex:1;border:0;background:none;border-radius:10px;padding:9px 4px;font:inherit;font-size:14px;font-weight:700;color:#3d5577}
.tb button.on{background:#fff;color:#0f2a4d;box-shadow:0 1px 4px rgba(0,0,0,.08)}
.card{background:#fff;border-radius:16px;padding:12px;box-shadow:0 2px 8px rgba(15,42,77,.06);margin-bottom:10px}
input[type=search]{width:100%;border:1.5px solid #d6deea;border-radius:12px;padding:10px 12px;font:inherit;font-size:15px;margin-bottom:8px}
.row{display:flex;align-items:center;gap:10px;padding:9px 2px;border-bottom:1px solid #eef2f8}.row:last-child{border:0}
.rk{flex:none;width:38px;height:38px;border-radius:50%;background:#eaf1fb;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
.r1 .rk{background:#f5b53f;color:#2a1e00}.r2 .rk{background:#d9dee7}.r3 .rk{background:#f1c9a5}
.nm{flex:1;min-width:0}.nm b{display:block;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.nm small{display:block;font-size:12px;color:#5a6b8c;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sc{text-align:right}.sc b{display:block;font-size:17px}.sc small{font-size:11.5px;color:#5a6b8c}
.sec{padding:8px 0;border-bottom:1px solid #eef2f8}.sec:last-child{border:0}.sec .l{display:flex;justify-content:space-between;font-size:14px}.bar{height:9px;border-radius:6px;background:#edf1f7;margin-top:5px;overflow:hidden}.bar i{display:block;height:100%;background:linear-gradient(90deg,#16a34a,#4ade80)}
.btn{display:block;width:100%;border:0;border-radius:14px;padding:13px;font:inherit;font-size:16px;font-weight:800;margin-top:8px;background:#0f2a4d;color:#fff}.btn.gd{background:linear-gradient(135deg,#ffe29a,#f5b53f);color:#2a1e00}.btn.ln{background:#fff;color:#0f2a4d;border:1.5px solid #c9d3e3}
.f{display:flex;gap:8px;align-items:center;font-size:14px;margin:6px 0}.f select{flex:1;border:1.5px solid #d6deea;border-radius:10px;padding:8px;font:inherit}
.msg{text-align:center;padding:40px 16px;color:#5a6b8c}.note{font-size:12.5px;color:#5a6b8c;margin-top:8px}
.btn.rm{background:#fdecec;color:#b42318;border:1.5px solid #f5c2c0;margin:10px 0 4px}.mini{flex:none;border:1.5px solid #c9d3e3;background:#fff;border-radius:10px;padding:7px 10px;font:inherit;font-size:13px;font-weight:700;color:#0f2a4d}
.su{background:#fff;border-radius:14px;padding:10px 12px;margin-bottom:10px;box-shadow:0 2px 8px rgba(15,42,77,.06)}.su>b{font-size:14px}.su div{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;margin-top:6px}.su span{text-align:center;font-size:11.5px;color:#5a6b8c;background:#f3f6fb;border-radius:10px;padding:6px 2px}.su span b{display:block;font-size:18px;color:#0f2a4d}
.sum{background:#fff8e6;border:1px solid #f5d590;border-radius:14px;padding:10px 12px;font-size:15px;line-height:1.5;margin-bottom:10px}
.k em{display:block;font-style:normal;font-size:10.5px;color:#8a97ad;line-height:1.25;margin-top:2px}.k2{grid-template-columns:repeat(3,minmax(0,1fr));margin:10px 0}
.row[data-i]{cursor:pointer}.ar{color:#9aa7bd;font-size:22px;margin-left:2px}.tip{margin:0 0 4px}.rk.live{color:#16a34a;background:#e8f7ed;font-size:18px}.u{font-size:12px;color:#5a6b8c}
.md{position:fixed;inset:0;background:rgba(7,21,52,.55);display:flex;align-items:flex-end;z-index:9}.mb{background:#eef2f8;width:100%;max-height:88vh;overflow:auto;border-radius:20px 20px 0 0;padding:16px 12px 24px;position:relative}
.mx{position:absolute;right:12px;top:12px;width:36px;height:36px;border-radius:50%;border:0;background:#dfe6f2;font-size:16px}.dh{display:flex;gap:12px;align-items:center;padding-right:44px}.dh b{font-size:18px}.dh small{display:block;font-size:12.5px;color:#5a6b8c}
.rk.big{width:52px;height:52px;font-size:18px;background:#f5b53f;color:#2a1e00}.card.flat{box-shadow:none}.sm{font-size:11.5px;color:#5a6b8c}
.tb button{font-size:13px;padding:9px 2px}
</style></head><body>
<div class="hd"><div class="t"><div class="lg"><img src="<?php echo esc_url(EH_URI . '/assets/upsssc-alert-logo.svg'); ?>" alt=""></div><div><b>UPSSSC <em>ALERT</em> · Admin</b><small id="who">Super TET Mock — Admin Panel</small></div></div>
<div class="rs" id="rs"></div><div class="st" id="st" hidden><i></i><span id="stt"></span></div></div>
<div class="wrap" id="app"><div class="msg">लोड हो रहा है…</div></div>
<script>
(function(){var T=window.Telegram&&Telegram.WebApp;if(T){T.ready();T.expand();try{T.setHeaderColor('#071534')}catch(e){}}
var REST=<?php echo wp_json_encode($rest); ?>,SITE=<?php echo wp_json_encode($site); ?>,INIT=T?T.initData:'',D=null,RID='',TAB='merit',Q='',OPEN=-1;
function $(i){return document.getElementById(i)}function esc(s){return String(s==null?'':s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
function api(p,body){return fetch(REST+p,{method:body?'POST':'GET',headers:Object.assign({'X-TG-Init':INIT},body?{'Content-Type':'application/json'}:{}),body:body?JSON.stringify(body):undefined}).then(function(r){return r.json().then(function(j){if(!r.ok)throw j;return j})})}
var MO=['जन','फ़र','मार्च','अप्रै','मई','जून','जुला','अग','सित','अक्टू','नवं','दिसं'];function dt(t){var d=new Date(t*1000);return d.getDate()+' '+MO[d.getMonth()]}
function mm(s){s=Math.max(0,s|0);return Math.floor(s/60)+' मि '+(s%60<10?'0':'')+(s%60)+' से'}
function dv(x){return /mob|android|iphone/i.test(x||'')?'📱 मोबाइल':(x?'💻 कंप्यूटर':'—')}
function load(){if(!INIT){$('app').innerHTML='<div class="msg">यह पेज Telegram बॉट के "Admin Panel" बटन से खोलें।</div>';return}
api('report'+(RID?'?round='+RID:'')).then(function(d){D=d;RID=d.round.id;draw()}).catch(function(e){$('app').innerHTML='<div class="msg">'+(e&&e.code==='rest_forbidden'?'आपको इस पैनल की अनुमति नहीं है। बॉट के owner से admin बनवाएँ।':'लोड नहीं हुआ — दोबारा खोलें।')+'</div>'})}
function rowH(x,i){return '<div class="row r'+x.rank+'" data-i="'+i+'"><div class="rk">'+x.rank+'</div><div class="nm"><b>'+esc(x.name)+'</b><small>'+esc(x.email)+' · '+esc(x.roll)+'</small></div><div class="sc"><b>'+x.score+'</b><small>'+x.pct+'%ile · '+x.c+'✓ '+x.w+'✗</small></div><span class="ar">›</span></div>'}
function detail(i){var x=D.rows[i],S=D.sections;if(!x)return;OPEN=i;
var h='<div class="md" id="md"><div class="mb"><button class="mx" id="mx" aria-label="बंद करें">✕</button><div class="dh"><div class="rk big">'+x.rank+'</div><div><b>'+esc(x.name)+'</b><small>'+esc(x.email)+'<br>रोल नं. '+esc(x.roll)+'</small></div></div>'+
'<div class="k k2"><div><b>'+x.score+'</b><span>अंक / 360</span></div><div><b>'+x.pct+'</b><span>परसेंटाइल</span></div><div class="g"><b>'+x.c+'</b><span>सही</span></div><div class="o"><b>'+x.w+'</b><span>गलत</span></div><div><b>'+(120-x.c-x.w)+'</b><span>छोड़े</span></div><div><b>'+x.min+'</b><span>मिनट लगे</span></div></div>'+
'<button class="btn rm" id="rmv">🚫 इसे मेरिट / रैंक से हटाओ</button><p class="note">कुल प्रयास: <b>'+x.att+'</b> (मेरिट में पहला) · '+dv(x.dev)+' · टैब/ऐप बदला: <b>'+x.tabs+'</b> बार'+(x.tabs>=5?' ⚠️':'')+'</p><div class="card flat"><b>सेक्शन-वार</b>'+
(x.secs||[]).map(function(p,k){var q=S[k]?S[k].q:0,mx=q*3,sc=p[0]*3-p[1],pc=mx?Math.max(0,sc)*100/mx:0;return '<div class="sec"><div class="l"><span>'+esc(S[k]?S[k].n:'')+'</span><b>'+sc+' / '+mx+'</b></div><div class="bar"><i style="width:'+pc+'%"></i></div><small class="sm">सही '+p[0]+' · गलत '+p[1]+' · छोड़े '+(q-p[0]-p[1])+'</small></div>'}).join('')+'</div></div></div>';
document.body.insertAdjacentHTML('beforeend',h);$('mx').onclick=close;
$('rmv').onclick=function(){var go=function(){$('rmv').disabled=true;api('remove',{round:RID,uid:x.uid}).then(function(j){close();if(j.ok){if(T&&T.showAlert)T.showAlert(x.name+' को मेरिट से हटा दिया। वापस जोड़ना हो तो ⬇ रिपोर्ट टैब में "हटाए गए" देखें।');load()}})};
var msg=x.name+' (रैंक '+x.rank+', '+x.score+' अंक) को मेरिट लिस्ट और रैंक से हटाना है?';if(T&&T.showConfirm)T.showConfirm(msg,function(ok){if(ok)go()});else if(confirm(msg))go()};$('md').onclick=function(e){if(e.target.id==='md')close()};if(T&&T.BackButton){T.BackButton.show();T.BackButton.onClick(close)}}
function close(){OPEN=-1;var m=$('md');if(m)m.remove();if(T&&T.BackButton){T.BackButton.hide();T.BackButton.offClick(close)}}
function draw(){var d=D,s=d.stats,now=d.now,live=now>=d.round.start&&now<d.round.end;var ser=d.round.series||'stet',SN={stet:'Super TET',tgt:'UP TGT',pgt:'UP PGT'};$('who').textContent='नमस्ते '+(d.me||'')+' · '+SN[ser]+' Mock'+(d.round.sub?' · '+d.round.sub:'');
var first=function(sr){var l=d.rounds.filter(function(r){return (r.series||'stet')===sr});if(sr==='stet')return d.cur;var x=l.filter(function(r){return r.ready})[0]||l[0];return x?x.id:''};
var h='<div class="sr">'+['stet','tgt','pgt'].map(function(sr){return '<button class="'+(sr===ser?'on':'')+'" data-s="'+sr+'">'+SN[sr]+'</button>'}).join('')+'</div>';
if(ser==='stet')h+=d.rounds.filter(function(r){return (r.series||'stet')==='stet'&&r.kind!=='practice'}).map(function(r,i){var lv=now>=r.start&&now<r.end,past=now>=r.end;return '<button class="'+(r.id===d.round.id?'on':'')+'" data-r="'+r.id+'"><b>मॉक '+(i+1)+'</b> · '+dt(r.start)+'<small>'+(lv?'🟢 लाइव':past?'समाप्त':'आगामी')+'</small></button>'}).join('');
if(ser==='stet'){var fl=d.rounds.filter(function(r){return (r.series||'stet')==='stet'&&r.kind==='practice'});if(fl.length)h+='<select id="rsel"><option value="">📘 Full Length Mock चुनें ('+fl.length+')</option>'+fl.map(function(r){return '<option value="'+r.id+'"'+(r.id===d.round.id?' selected':'')+'>'+esc(String(r.name).replace(/^SUPER TET - /,''))+'</option>'}).join('')+'</select>'}
else h+='<select id="rsel">'+d.rounds.filter(function(r){return r.series===ser}).map(function(r){return '<option value="'+r.id+'"'+(r.id===d.round.id?' selected':'')+'>'+esc(r.sub)+(r.kind==='sec'?' (सेक्शनल)':'')+(r.ready?' — लाइव':' — Coming Soon')+'</option>'}).join('')+'</select>';
$('rs').innerHTML=h;
[].forEach.call($('rs').querySelectorAll('[data-r]'),function(b){b.onclick=function(){RID=b.dataset.r;TAB='merit';load()}});
[].forEach.call($('rs').querySelectorAll('[data-s]'),function(b){b.onclick=function(){var x=first(b.dataset.s);if(x){RID=x;TAB='merit';load()}}});
if($('rsel'))$('rsel').onchange=function(){if(!this.value)return;RID=this.value;TAB='merit';load()};
$('st').hidden=false;$('stt').textContent=live?'लाइव — रात '+new Date(d.round.end*1000).toLocaleTimeString('hi-IN',{hour:'numeric',minute:'2-digit'})+' तक खुला':(now<d.round.start?'अभी शुरू नहीं हुआ':'मॉक समाप्त — अंतिम परिणाम');$('st').querySelector('i').style.background=live?'#22c55e':'#94a3b8';
var sum=live?'अभी <b>'+s.live+'</b> लोग पेपर दे रहे हैं, <b>'+s.merit+'</b> ने सबमिट कर दिया है। अब तक का टॉप स्कोर <b>'+s.max+'</b> और औसत <b>'+s.avg+'</b> अंक है।':(s.merit?'कुल <b>'+s.merit+'</b> उम्मीदवार मेरिट में रहे। टॉप स्कोर <b>'+s.max+'</b>, औसत <b>'+s.avg+'</b> अंक।':'अभी कोई सबमिशन नहीं है।');
var g=d.signups,h='<div class="sum">📌 '+sum+'</div>'+(g?'<div class="su"><b>👤 वेबसाइट साइन-अप</b><div><span><b>'+g.total+'</b>कुल</span><span><b>'+g.today+'</b>आज</span><span><b>'+g.week+'</b>7 दिन</span><span><b>'+g.mock+'</b>इस मॉक से</span></div></div>':'')+'<div class="k"><div><b>'+s.users+'</b><span>पेज खोला</span><em>लॉग-इन करके मॉक पेज देखा</em></div><div class="g"><b>'+s.live+'</b><span>अभी दे रहे</span><em>पिछले 2 मिनट में सक्रिय</em></div><div><b>'+s.merit+'</b><span>मेरिट में</span><em>पहला प्रयास सबमिट</em></div>'+
'<div><b>'+s.max+'</b><span>टॉप स्कोर</span><em>360 में से</em></div><div><b>'+s.avg+'</b><span>औसत अंक</span><em>मेरिट वालों का</em></div><div class="o"><b>'+s.attempts+'</b><span>कुल प्रयास</span><em>दोबारा वाले मिलाकर</em></div></div>';
h+='<div class="tb"><button data-t="merit" class="'+(TAB==='merit'?'on':'')+'">🏆 मेरिट</button><button data-t="now" class="'+(TAB==='now'?'on':'')+'">🟢 अभी ('+(d.writing||[]).length+')</button><button data-t="sec" class="'+(TAB==='sec'?'on':'')+'">📊 सेक्शन</button><button data-t="rep" class="'+(TAB==='rep'?'on':'')+'">⬇ रिपोर्ट</button></div>';
if(TAB==='merit'){var rows=[];d.rows.forEach(function(x,i){if(!Q||(x.name+' '+x.email+' '+x.roll).toLowerCase().indexOf(Q)>=0)rows.push([x,i])});
h+='<div class="card"><input type="search" id="q" placeholder="नाम, ईमेल या रोल नं. खोजें" value="'+esc(Q)+'"><p class="note tip">👆 किसी नाम पर टैप करें — पूरी जानकारी और सेक्शन-वार अंक दिखेंगे।</p>'+(rows.length?rows.slice(0,200).map(function(p){return rowH(p[0],p[1])}).join(''):'<div class="msg">अभी कोई नहीं</div>')+'</div>'+(rows.length>200?'<p class="note">पहले 200 दिखाए गए हैं — पूरी सूची रिपोर्ट से मँगाएँ।</p>':'')}
else if(TAB==='now'){var w=d.writing||[];h+='<div class="card">'+(w.length?'<p class="note tip">इस समय पेपर दे रहे उम्मीदवार (पिछले 2 मिनट में सक्रिय)</p>'+w.map(function(x){return '<div class="row"><div class="rk live">●</div><div class="nm"><b>'+esc(x.name)+'</b><small>'+esc(x.email)+' · '+dv(x.dev)+(x.att>1?' · प्रयास '+x.att:'')+'</small></div><div class="sc"><b>'+x.answered+'<span class="u">/120</span></b><small>⏱ '+mm(x.left)+' बचे'+(x.tabs?' · टैब '+x.tabs:'')+'</small></div></div>'}).join(''):'<div class="msg">'+(live?'इस समय कोई पेपर नहीं दे रहा।':'मॉक लाइव नहीं है।')+'</div>')+'</div>'}
else if(TAB==='sec'){h+='<div class="card">'+d.sections.map(function(x){var mx=x.q*3,p=Math.max(0,Math.min(100,x.avg*100/mx));return '<div class="sec"><div class="l"><span>'+esc(x.n)+'</span><b>'+x.avg+' / '+mx+'</b></div><div class="bar"><i style="width:'+p+'%"></i></div></div>'}).join('')+'</div><p class="note">मेरिट में शामिल ('+s.merit+') उम्मीदवारों का औसत · औसत सही '+s.avgc+' · औसत गलत '+s.avgw+' · मीडियन '+s.median+'</p>'}
else{var m=d.mail,rmv=d.removed||[];h+=(rmv.length?'<div class="card"><b>🚫 मेरिट से हटाए गए ('+rmv.length+')</b>'+rmv.map(function(x){return '<div class="row"><div class="nm"><b>'+esc(x.name)+'</b><small>'+esc(x.email)+' · '+x.score+' अंक'+(x.by?' · हटाया: '+esc(x.by):'')+'</small></div><button class="mini" data-undo="'+x.uid+'">↩ वापस जोड़ो</button></div>'}).join('')+'</div>':'')+'<div class="card"><b>🏆 टॉप-10 पोस्ट</b><p class="note">टॉप 10 का तैयार संदेश इसी बॉट चैट में आएगा — वहीं से UPSSSC ALERT चैनल पर फ़ॉरवर्ड कर दें (ईमेल नहीं होंगे)।</p><button class="btn gd" id="top10">🏆 टॉप-10 पोस्ट बनाओ</button><p class="note" id="tn"></p></div>'+
'<div class="card"><b>मेरिट लिस्ट Telegram पर मँगाएँ (CSV — Excel में खुलेगी)</b><div class="f">टॉप<select id="top"><option value="0">सभी</option><option value="10">10</option><option value="50">50</option><option value="100">100</option></select></div>'+
'<button class="btn ln" data-m="1">🔒 Masked भेजो (ईमेल छिपे)</button><button class="btn" data-m="0">📋 Unmasked भेजो (ईमेल सहित)</button><p class="note" id="sn">फ़ाइल इसी बॉट चैट में आएगी।</p></div>'+
'<div class="card"><b>PDF रिपोर्ट (ब्रांडिंग सहित)</b><p class="note">पूरी PDF मेरिट रिपोर्ट वेबसाइट के Admin Panel से बनती है।</p><button class="btn ln" id="web">🌐 वेबसाइट Admin Panel खोलें</button></div>'+
(m?'<div class="card"><b>📧 स्कोरकार्ड मेल</b> — '+(m.on?'चालू':'बंद')+'<p class="note">भेजे '+m.sent+' · बाकी '+m.pending+' · फेल '+m.fail+'</p></div>':'')}
$('app').innerHTML=h;
[].forEach.call(document.querySelectorAll('.tb button'),function(b){b.onclick=function(){TAB=b.dataset.t;draw()}});
[].forEach.call(document.querySelectorAll('.row[data-i]'),function(r){r.onclick=function(){detail(+r.dataset.i)}});
var q=$('q');if(q){q.oninput=function(){Q=q.value.trim().toLowerCase();var p=q.selectionStart;draw();var n=$('q');n.focus();try{n.setSelectionRange(p,p)}catch(e){}}}
var t1=$('top10');if(t1)t1.onclick=function(){t1.disabled=true;$('tn').textContent='बना रहे हैं…';api('top',{round:RID}).then(function(j){$('tn').textContent=j.ok?'✅ पोस्ट बॉट चैट में आ गई — फ़ॉरवर्ड कर दें।':'❌ नहीं बनी, दोबारा कोशिश करें।'}).catch(function(){$('tn').textContent='❌ नहीं बनी।'}).then(function(){t1.disabled=false})};
[].forEach.call(document.querySelectorAll('[data-m]'),function(b){b.onclick=function(){b.disabled=true;$('sn').textContent='भेज रहे हैं…';api('send',{round:RID,masked:b.dataset.m==='1',top:+$('top').value}).then(function(j){$('sn').textContent=j.ok?'✅ भेज दी — बॉट चैट देखें।':'❌ नहीं भेज पाए, दोबारा कोशिश करें।';if(j.ok&&T&&T.HapticFeedback)T.HapticFeedback.notificationOccurred('success')}).catch(function(){$('sn').textContent='❌ नहीं भेज पाए।'}).then(function(){b.disabled=false})}});
[].forEach.call(document.querySelectorAll('[data-undo]'),function(b){b.onclick=function(){b.disabled=true;api('remove',{round:RID,uid:+b.dataset.undo,undo:1}).then(function(){load()})}});
var w2=$('web');if(w2)w2.onclick=function(){if(T)T.openLink(SITE);else location.href=SITE}}
load();setInterval(function(){if(TAB!=='rep'&&OPEN<0&&!document.hidden&&!(document.activeElement&&document.activeElement.id==='q'))load()},20000);
})();
</script></body></html><?php
    exit;
});
