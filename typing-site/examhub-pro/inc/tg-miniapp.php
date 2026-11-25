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
        $d['rounds'] = array_values(array_filter($d['rounds'], function ($x) { return $x['id'][0] === 'm'; }));
        $d['cur'] = eh_stm_round()['id'];
        if (function_exists('eh_stm_mail_counts')) { $d['mail'] = eh_stm_mail_counts(); $d['mail']['on'] = (int) eh_stm_mail_opt()['on']; }
        $d['me'] = eh_tgapp_user()['first_name'] ?? '';
        return $d;
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
</style></head><body>
<div class="hd"><div class="t"><div class="lg"><img src="<?php echo esc_url(EH_URI . '/assets/upsssc-alert-logo.svg'); ?>" alt=""></div><div><b>UPSSSC <em>ALERT</em> · Admin</b><small id="who">Super TET Mock — Admin Panel</small></div></div>
<div class="rs" id="rs"></div><div class="st" id="st" hidden><i></i><span id="stt"></span></div></div>
<div class="wrap" id="app"><div class="msg">लोड हो रहा है…</div></div>
<script>
(function(){var T=window.Telegram&&Telegram.WebApp;if(T){T.ready();T.expand();try{T.setHeaderColor('#071534')}catch(e){}}
var REST=<?php echo wp_json_encode($rest); ?>,SITE=<?php echo wp_json_encode($site); ?>,INIT=T?T.initData:'',D=null,RID='',TAB='live',Q='',TM=0;
function $(i){return document.getElementById(i)}function esc(s){return String(s==null?'':s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
function api(p,body){return fetch(REST+p,{method:body?'POST':'GET',headers:Object.assign({'X-TG-Init':INIT},body?{'Content-Type':'application/json'}:{}),body:body?JSON.stringify(body):undefined}).then(function(r){return r.json().then(function(j){if(!r.ok)throw j;return j})})}
var MO=['जन','फ़र','मार्च','अप्रै','मई','जून','जुला','अग','सित','अक्टू','नवं','दिसं'];function dt(t){var d=new Date(t*1000);return d.getDate()+' '+MO[d.getMonth()]}
function load(){if(!INIT){$('app').innerHTML='<div class="msg">यह पेज Telegram बॉट के "Admin Panel" बटन से खोलें।</div>';return}
api('report'+(RID?'?round='+RID:'')).then(function(d){D=d;RID=d.round.id;draw()}).catch(function(e){$('app').innerHTML='<div class="msg">'+(e&&e.code==='rest_forbidden'?'आपको इस पैनल की अनुमति नहीं है। बॉट के owner से admin बनवाएँ।':'लोड नहीं हुआ — दोबारा खोलें।')+'</div>'})}
function draw(){var d=D,s=d.stats,now=d.now;$('who').textContent='नमस्ते '+(d.me||'')+' · Super TET Mock';
$('rs').innerHTML=d.rounds.map(function(r,i){var live=now>=r.start&&now<r.end,past=now>=r.end;return '<button class="'+(r.id===d.round.id?'on':'')+'" data-r="'+r.id+'"><b>मॉक '+(i+1)+'</b> · '+dt(r.start)+'<small>'+(live?'🟢 लाइव':past?'समाप्त':'आगामी')+'</small></button>'}).join('');
[].forEach.call($('rs').querySelectorAll('button'),function(b){b.onclick=function(){RID=b.dataset.r;load()}});
var live=now>=d.round.start&&now<d.round.end;$('st').hidden=false;$('stt').textContent=live?'लाइव — रात '+new Date(d.round.end*1000).toLocaleTimeString('hi-IN',{hour:'numeric',minute:'2-digit'})+' तक':(now<d.round.start?'अभी शुरू नहीं हुआ':'मॉक समाप्त');$('st').querySelector('i').style.background=live?'#22c55e':'#94a3b8';
var h='<div class="k"><div><b>'+s.users+'</b><span>पेज खोला</span></div><div class="g"><b>'+s.live+'</b><span>अभी दे रहे</span></div><div><b>'+s.merit+'</b><span>मेरिट में</span></div>'+
'<div><b>'+s.max+'</b><span>टॉप स्कोर</span></div><div><b>'+s.avg+'</b><span>औसत अंक</span></div><div class="o"><b>'+s.attempts+'</b><span>कुल प्रयास</span></div></div>';
h+='<div class="tb"><button data-t="live" class="'+(TAB==='live'?'on':'')+'">🏆 मेरिट</button><button data-t="sec" class="'+(TAB==='sec'?'on':'')+'">📊 सेक्शन</button><button data-t="rep" class="'+(TAB==='rep'?'on':'')+'">⬇ रिपोर्ट</button></div>';
if(TAB==='live'){var rows=d.rows.filter(function(x){return !Q||(x.name+' '+x.email+' '+x.roll).toLowerCase().indexOf(Q)>=0});
h+='<div class="card"><input type="search" id="q" placeholder="नाम, ईमेल या रोल नं. खोजें" value="'+esc(Q)+'">'+(rows.length?rows.slice(0,200).map(function(x){return '<div class="row r'+x.rank+'"><div class="rk">'+x.rank+'</div><div class="nm"><b>'+esc(x.name)+'</b><small>'+esc(x.email)+' · '+esc(x.roll)+'</small></div><div class="sc"><b>'+x.score+'</b><small>'+x.pct+'%ile · '+x.c+'✓ '+x.w+'✗</small></div></div>'}).join(''):'<div class="msg">अभी कोई नहीं</div>')+'</div>'+(rows.length>200?'<p class="note">पहले 200 दिखाए गए हैं — पूरी सूची रिपोर्ट से मँगाएँ।</p>':'')}
else if(TAB==='sec'){h+='<div class="card">'+d.sections.map(function(x){var mx=x.q*3,p=Math.max(0,Math.min(100,x.avg*100/mx));return '<div class="sec"><div class="l"><span>'+esc(x.n)+'</span><b>'+x.avg+' / '+mx+'</b></div><div class="bar"><i style="width:'+p+'%"></i></div></div>'}).join('')+'</div><p class="note">मेरिट में शामिल ('+s.merit+') उम्मीदवारों का औसत · औसत सही '+s.avgc+' · औसत गलत '+s.avgw+' · मीडियन '+s.median+'</p>'}
else{var m=d.mail;h+='<div class="card"><b>मेरिट लिस्ट Telegram पर मँगाएँ (CSV — Excel में खुलेगी)</b><div class="f">टॉप<select id="top"><option value="0">सभी</option><option value="10">10</option><option value="50">50</option><option value="100">100</option></select></div>'+
'<button class="btn gd" data-m="1">🔒 Masked भेजो (ईमेल छिपे)</button><button class="btn" data-m="0">📋 Unmasked भेजो (ईमेल सहित)</button><p class="note" id="sn">फ़ाइल इसी बॉट चैट में आएगी।</p></div>'+
'<div class="card"><b>PDF रिपोर्ट (ब्रांडिंग सहित)</b><p class="note">पूरी PDF मेरिट रिपोर्ट वेबसाइट के Admin Panel से बनती है।</p><button class="btn ln" id="web">🌐 वेबसाइट Admin Panel खोलें</button></div>'+
(m?'<div class="card"><b>📧 स्कोरकार्ड मेल</b> — '+(m.on?'चालू':'बंद')+'<p class="note">भेजे '+m.sent+' · बाकी '+m.pending+' · फेल '+m.fail+'</p></div>':'')}
$('app').innerHTML=h;
[].forEach.call(document.querySelectorAll('.tb button'),function(b){b.onclick=function(){TAB=b.dataset.t;draw()}});
var q=$('q');if(q){q.oninput=function(){Q=q.value.trim().toLowerCase();var p=q.selectionStart;draw();var n=$('q');n.focus();try{n.setSelectionRange(p,p)}catch(e){}}}
[].forEach.call(document.querySelectorAll('[data-m]'),function(b){b.onclick=function(){b.disabled=true;$('sn').textContent='भेज रहे हैं…';api('send',{round:RID,masked:b.dataset.m==='1',top:+$('top').value}).then(function(j){$('sn').textContent=j.ok?'✅ भेज दी — बॉट चैट देखें।':'❌ नहीं भेज पाए, दोबारा कोशिश करें।';if(j.ok&&T&&T.HapticFeedback)T.HapticFeedback.notificationOccurred('success')}).catch(function(){$('sn').textContent='❌ नहीं भेज पाए।'}).then(function(){b.disabled=false})}});
var w=$('web');if(w)w.onclick=function(){if(T)T.openLink(SITE);else location.href=SITE}}
load();TM=setInterval(function(){if(TAB!=='rep'&&!document.hidden&&!(document.activeElement&&document.activeElement.id==='q'))load()},20000);
})();
</script></body></html><?php
    exit;
});
