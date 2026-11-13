<?php
/* Telegram stats bot: owner + admins see visitors, tests, live typing and sign-ups from Telegram.
   The bot token is entered by the site owner in WP admin; Telegram calls the site through a secret webhook. */
if (!defined('ABSPATH')) exit;

/* ---------- hourly visitor table (for "Last 1 hour") ---------- */
define('EH_VISH_VER', '1');
function eh_vish_table() { global $wpdb; return $wpdb->prefix . 'eh_visits_h'; }
add_action('init', function () {
    if (get_option('eh_vish_ver') === EH_VISH_VER) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . eh_vish_table() . " (
        hr datetime NOT NULL,
        vid char(16) NOT NULL DEFAULT '',
        views int(11) NOT NULL DEFAULT 1,
        PRIMARY KEY  (hr,vid)
    ) " . $wpdb->get_charset_collate() . ';');
    update_option('eh_vish_ver', EH_VISH_VER);
});
add_action('template_redirect', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || is_admin() || is_feed() || is_preview() || is_robots() || is_404()) return;
    if (get_option('eh_vish_ver') !== EH_VISH_VER) return;
    if (is_user_logged_in() && current_user_can('edit_posts')) return;
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse|facebookexternalhit|whatsapp|telegram/i', $ua)) return;
    global $wpdb;
    $vid = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $ua . '|' . wp_salt('nonce')), 0, 16);
    $wpdb->query($wpdb->prepare('INSERT INTO ' . eh_vish_table() . ' (hr, vid, views) VALUES (%s, %s, 1) ON DUPLICATE KEY UPDATE views = views + 1', current_time('Y-m-d H:00:00'), $vid));
}, 21);

/* ---------- Telegram API ---------- */
function eh_tgb_token() { return trim((string) get_option('eh_tgb_token', '')); }
function eh_tgb_api($method, $args = []) {
    $tok = eh_tgb_token(); if ($tok === '') return null;
    $r = wp_remote_post('https://api.telegram.org/bot' . $tok . '/' . $method, ['timeout' => 15, 'headers' => ['Content-Type' => 'application/json'], 'body' => wp_json_encode($args)]);
    if (is_wp_error($r)) return null;
    return json_decode(wp_remote_retrieve_body($r), true);
}

/* ---------- admins ---------- */
function eh_tgb_admins() { $a = get_option('eh_tgb_admins', []); return is_array($a) ? $a : []; }
function eh_tgb_extra_ids() { return array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', (string) get_option('eh_tgb_ids', ''))), 'is_numeric')); }
function eh_tgb_role($id) {
    $a = eh_tgb_admins(); $id = (string) $id;
    if (isset($a[$id])) return $a[$id]['r'];
    return in_array($id, eh_tgb_extra_ids(), true) ? 'admin' : '';
}

/* ---------- numbers ---------- */
function eh_tgb_range($k) {
    $now = time(); /* wp_date() adds the site time zone itself */ $d = wp_date('Y-m-d', $now);
    switch ($k) {
        case 'hour':  return [wp_date('Y-m-d H:i:s', $now - 3600), wp_date('Y-m-d H:i:s', $now), 'पिछला 1 घंटा'];
        case 'yday':  $y = wp_date('Y-m-d', $now - 86400); return [$y . ' 00:00:00', $y . ' 23:59:59', 'कल (' . wp_date('d M', $now - 86400) . ')'];
        case '7d':    return [wp_date('Y-m-d', $now - 6 * 86400) . ' 00:00:00', $d . ' 23:59:59', 'पिछले 7 दिन'];
        case 'month': return [wp_date('Y-m-01', $now) . ' 00:00:00', $d . ' 23:59:59', 'इस महीने (' . wp_date('F', $now) . ')'];
        case 'all':   return ['2000-01-01 00:00:00', $d . ' 23:59:59', 'शुरू से अब तक'];
        default:      return [$d . ' 00:00:00', $d . ' 23:59:59', 'आज (' . wp_date('d M Y', $now) . ')'];
    }
}
function eh_tgb_stats($k) {
    global $wpdb; [$a, $b, $lab] = eh_tgb_range($k); $t = eh_table(); $p = eh_stats_people_sql();
    if ($k === 'hour') {
        $h = eh_vish_table(); $from = substr($a, 0, 13) . ':00:00';
        $vis = $wpdb->get_row($wpdb->prepare("SELECT COUNT(DISTINCT vid) v, COALESCE(SUM(views),0) pv FROM $h WHERE hr >= %s", $from), ARRAY_A);
    } else {
        $v = eh_vis_table();
        $vis = $wpdb->get_row($wpdb->prepare("SELECT COUNT(DISTINCT vid) v, COALESCE(SUM(views),0) pv FROM $v WHERE day BETWEEN %s AND %s", substr($a, 0, 10), substr($b, 0, 10)), ARRAY_A);
    }
    $ts = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) n, COUNT(DISTINCT $p) ppl, COALESCE(SUM(pass),0) ok, COALESCE(SUM(live_date IS NOT NULL),0) lv, COALESCE(ROUND(AVG(net)),0) avg FROM $t WHERE created BETWEEN %s AND %s", $a, $b), ARRAY_A);
    $su = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered BETWEEN %s AND %s", get_gmt_from_date($a), get_gmt_from_date($b)));
    $n = function ($x) { return number_format_i18n((int) $x); };
    $row = function ($l, $v) { return str_pad($l, 15, ' ') . str_pad($v, 9, ' ', STR_PAD_LEFT) . "\n"; };
    $tn = (int) $ts['n']; $pct = $tn ? round((int) $ts['ok'] * 100 / $tn) . '%' : '-';
    $o = '📊 <b>' . esc_html($lab) . '</b>' . "\n";
    $o .= "\n👀 <b>Website</b>\n<pre>" . $row('Visitors', $n($vis['v'] ?? 0)) . $row('Page views', $n($vis['pv'] ?? 0)) . '</pre>';
    $o .= "\n⌨️ <b>Typing tests</b>\n<pre>" . $row('Total tests', $n($tn)) . $row('People', $n($ts['ppl'])) . $row('Qualified', $n($ts['ok'])) . $row('Not qualified', $n($tn - (int) $ts['ok'])) . $row('Pass rate', $pct) . $row('Live typing', $n($ts['lv'])) . $row('Avg net speed', (int) $ts['avg'] . ' WPM') . '</pre>';
    $o .= "\n👤 <b>New sign-ups</b>\n<pre>" . $row('Sign-ups', $n($su)) . '</pre>';
    if ($k === 'hour' && strtotime((string) get_option('eh_tgb_since', '2000-01-01')) > current_time('timestamp') - 7200) $o .= "\n\n<i>घंटे वाली गिनती बॉट जुड़ने के बाद से शुरू हुई है।</i>";
    return $o;
}
function eh_tgb_live() {
    global $wpdb; $t = eh_table(); $d = current_time('Y-m-d');
    $n = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE live_date = %s", $d));
    $ppl = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(DISTINCT ' . eh_stats_people_sql() . ") FROM $t WHERE live_date = %s", $d));
    $rows = $wpdb->get_results($wpdb->prepare("SELECT name, user_id, net, acc, exam FROM $t WHERE live_date = %s ORDER BY net DESC, acc DESC LIMIT 5", $d), ARRAY_A);
    $o = '⚡ <b>Live Typing · आज</b>' . "\n\nTests: <b>" . number_format_i18n($n) . '</b>  ·  People: ' . number_format_i18n($ppl) . "\n\n🏆 <b>Top 5</b>\n";
    if (!$rows) return $o . 'अभी किसी ने टेस्ट नहीं दिया।';
    $md = ['🥇', '🥈', '🥉', '4.', '5.'];
    foreach ($rows as $i => $r) { $e = eh_exam($r['exam']); $o .= $md[$i] . ' ' . esc_html(eh_display_name($r)) . ' — <b>' . (int) $r['net'] . ' WPM</b> · ' . esc_html($r['acc']) . '%' . ($e ? ' · ' . esc_html($e['name']) : '') . "\n"; }
    return $o;
}
function eh_tgb_3284() {
    global $wpdb; $t = eh_table(); $d = current_time('Y-m-d'); $pp = eh_stats_people_sql();
    $q = function ($extra) use ($wpdb, $t, $pp) { return $wpdb->get_row("SELECT COUNT(*) n, COUNT(DISTINCT $pp) ppl, COALESCE(SUM(pass),0) ok, COALESCE(ROUND(AVG(net)),0) avg FROM $t WHERE passage LIKE 'x3284%' $extra", ARRAY_A); };
    $all = $q(''); $today = $q($wpdb->prepare('AND created >= %s', $d . ' 00:00:00'));
    $en = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE passage LIKE 'x3284en%'"); $hi = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE passage LIKE 'x3284hi%'");
    $n = function ($x) { return number_format_i18n((int) $x); };
    $row = function ($l, $v) { return str_pad($l, 15, ' ') . str_pad($v, 9, ' ', STR_PAD_LEFT) . "\n"; };
    $o = '🔴 <b>UPSSSC 3284 Pattern Passage</b>' . "\n";
    $o .= "\n📅 <b>आज</b>\n<pre>" . $row('Tests', $n($today['n'])) . $row('People', $n($today['ppl'])) . $row('Qualified', $n($today['ok'])) . $row('Avg net speed', (int) $today['avg'] . ' WPM') . '</pre>';
    $o .= "\n🌐 <b>शुरू से अब तक</b>\n<pre>" . $row('Tests', $n($all['n'])) . $row('People', $n($all['ppl'])) . $row('Qualified', $n($all['ok'])) . $row('English tests', $n($en)) . $row('Hindi tests', $n($hi)) . $row('Avg net speed', (int) $all['avg'] . ' WPM') . '</pre>';
    $rows = $wpdb->get_results("SELECT name, user_id, net, acc FROM $t WHERE passage LIKE 'x3284%' ORDER BY net DESC, acc DESC LIMIT 5", ARRAY_A);
    if ($rows) { $md = ['🥇', '🥈', '🥉', '4.', '5.']; $o .= "\n🏆 <b>Top 5</b>\n"; foreach ($rows as $i => $r) $o .= $md[$i] . ' ' . esc_html(eh_display_name($r)) . ' — <b>' . (int) $r['net'] . ' WPM</b> · ' . esc_html($r['acc']) . "%\n"; }
    return $o;
}
function eh_tgb_top() {
    global $wpdb; $t = eh_table(); $d = current_time('Y-m-d');
    $rows = $wpdb->get_results($wpdb->prepare("SELECT exam, COUNT(*) n FROM $t WHERE created >= %s GROUP BY exam ORDER BY n DESC LIMIT 8", $d . ' 00:00:00'), ARRAY_A);
    $o = '🏆 <b>आज सबसे ज़्यादा टेस्ट वाले exam</b>' . "\n\n";
    if (!$rows) return $o . 'आज अभी कोई टेस्ट नहीं हुआ।';
    foreach ($rows as $i => $r) { $e = eh_exam($r['exam']); $o .= ($i + 1) . '. ' . esc_html($e ? $e['name'] : ($r['exam'] === 'demo' ? 'Demo Typing' : $r['exam'])) . ' — <b>' . number_format_i18n($r['n']) . "</b>\n"; }
    return $o;
}
function eh_tgb_users() {
    global $wpdb; $d = current_time('Y-m-d');
    $today = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= %s", get_gmt_from_date($d . ' 00:00:00')));
    $wk = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= %s", get_gmt_from_date(wp_date('Y-m-d', time() - 6 * 86400) . ' 00:00:00')));
    $all = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
    return '👤 <b>Sign-ups</b>' . "\n\nआज: <b>" . number_format_i18n($today) . "</b>\nपिछले 7 दिन: " . number_format_i18n($wk) . "\nकुल यूज़र: <b>" . number_format_i18n($all) . '</b>';
}

/* ---------- menus ---------- */
function eh_tgb_menu_kb($role) {
    $kb = [
        [['text' => '📊 Today', 'callback_data' => 'r:today'], ['text' => '⏱ Last 1 hour', 'callback_data' => 'r:hour']],
        [['text' => '📅 Yesterday', 'callback_data' => 'r:yday'], ['text' => '🗓 Last 7 days', 'callback_data' => 'r:7d']],
        [['text' => '📆 This month', 'callback_data' => 'r:month'], ['text' => '🌐 Full (All time)', 'callback_data' => 'r:all']],
        [['text' => '⚡ Live Typing', 'callback_data' => 'live'], ['text' => '🏆 Top exams', 'callback_data' => 'top']],
        [['text' => '🔴 3284 Pattern Passage', 'callback_data' => 'p3284']],
        [['text' => '👤 Sign-ups', 'callback_data' => 'users'], ['text' => '👥 Admins', 'callback_data' => 'admins']],
    ];
    if ($role === 'owner') { $kb[] = [['text' => '📝 पैसेज जोड़ें', 'callback_data' => 'pa'], ['text' => '📋 मेरे पैसेज', 'callback_data' => 'pl']]; $kb[] = [['text' => '➕ Add admin', 'callback_data' => 'add'], ['text' => '🔄 Update theme', 'callback_data' => 'upd']]; }
    return ['inline_keyboard' => $kb];
}
function eh_tgb_back_kb($again) { return ['inline_keyboard' => [[['text' => '🔄 Refresh', 'callback_data' => $again], ['text' => '☰ Menu', 'callback_data' => 'menu']]]]; }
function eh_tgb_menu_text() { return '📋 <b>' . esc_html(eh_opt('brand_typing') ?: get_bloginfo('name')) . ' — Stats Bot</b>' . "\nक्या देखना है? नीचे बटन दबाइए।"; }

function eh_tgb_admins_view($role) {
    $o = '👥 <b>Admins</b>' . "\n\n"; $kb = [];
    foreach (eh_tgb_admins() as $id => $a) {
        $o .= ($a['r'] === 'owner' ? '👑 ' : '• ') . esc_html($a['n']) . ' <code>' . esc_html($id) . '</code>' . ($a['r'] === 'owner' ? ' (Owner)' : '') . "\n";
        if ($role === 'owner' && $a['r'] !== 'owner') $kb[] = [['text' => '❌ ' . mb_substr($a['n'], 0, 24), 'callback_data' => 'del:' . $id]];
    }
    foreach (eh_tgb_extra_ids() as $id) if (!isset(eh_tgb_admins()[$id])) $o .= '• ID <code>' . esc_html($id) . "</code> (WP admin से जोड़ा)\n";
    $kb[] = [['text' => '☰ Menu', 'callback_data' => 'menu']];
    return [$o, ['inline_keyboard' => $kb]];
}

/* ---------- passages from the bot (owner only) ---------- */
function eh_tgb_pstate($id, $v = null) {
    $st = get_option('eh_tgb_pstate', []); if (!is_array($st)) $st = [];
    if ($v === null) return $st[$id] ?? null;
    if ($v === false) unset($st[$id]); else $st[$id] = $v + ['t' => time()];
    update_option('eh_tgb_pstate', $st, false); return $v;
}
function eh_tgb_lvname($l) { return ['E' => 'Easy', 'M' => 'Moderate', 'H' => 'Hard', 'S' => 'Super Hard'][$l] ?? $l; }
function eh_tgb_pclean($t) {
    $t = str_replace(["\r\n", "\r", "\n", "\t", "\u{00A0}", "\u{200B}"], ' ', (string) $t);
    $t = str_replace(['“', '”', '‘', '’'], ['"', '"', "'", "'"], $t);
    return trim(preg_replace('/ {2,}/u', ' ', $t));
}
function eh_tgb_pwarn($lang, $t) {
    $w = [];
    $hi = preg_match_all('/[\x{0900}-\x{097F}]/u', $t); $lat = preg_match_all('/[A-Za-z]/', $t);
    if ($lang === 'hi' && $hi < 20) $w[] = 'यह हिंदी पैसेज नहीं लग रहा';
    if ($lang === 'en' && $hi > 0) $w[] = 'English पैसेज में हिंदी अक्षर हैं';
    if ($lang === 'hi' && $lat) $w[] = 'हिंदी में ' . $lat . ' अंग्रेज़ी अक्षर हैं';
    if ($lang === 'hi' && preg_match('/[";%₹]/u', $t)) $w[] = 'हिंदी में " ; % ₹ जैसे चिह्न हैं (Krutidev में दिक्कत)';
    return $w;
}
function eh_tgb_purge() { if (function_exists('wp_cache_flush')) wp_cache_flush(); do_action('litespeed_purge_all'); if (function_exists('rocket_clean_domain')) rocket_clean_domain(); if (function_exists('w3tc_flush_all')) w3tc_flush_all(); }
function eh_tgb_plist_view() {
    $bot = get_option('eh_bot_passages', []); if (!is_array($bot)) $bot = [];
    usort($bot, function ($x, $y) { return strcmp($y['added'] ?? '', $x['added'] ?? ''); });
    $t = '📋 <b>Bot से डाले गए पैसेज</b> (' . count($bot) . ')' . "\n";
    $kb = [];
    foreach (array_slice($bot, 0, 15) as $i => $p) {
        $t .= "\n" . ($i + 1) . '. ' . ($p['lang'] === 'hi' ? '🇮🇳' : '🇬🇧') . ' <b>' . esc_html(mb_substr($p['title'], 0, 40)) . '</b> · ' . eh_tgb_lvname($p['level']) . ' · ' . (int) $p['words'] . ' शब्द · ' . esc_html(date('d M h:i A', strtotime($p['added'])));
        $kb[] = [['text' => '❌ ' . ($i + 1) . '. ' . mb_substr($p['title'], 0, 28), 'callback_data' => 'pd:' . $p['id']]];
    }
    if (!$bot) $t .= "\nअभी कोई नहीं।";
    $kb[] = [['text' => '📝 पैसेज जोड़ें', 'callback_data' => 'pa'], ['text' => '☰ Menu', 'callback_data' => 'menu']];
    return [$t, ['inline_keyboard' => $kb]];
}

/* ---------- webhook ---------- */
add_action('rest_api_init', function () {
    register_rest_route('eh/v1', '/tgbot', ['methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'eh_tgb_hook']);
});
function eh_tgb_hook(WP_REST_Request $req) {
    $sec = (string) get_option('eh_tgb_secret', '');
    if ($sec === '' || !hash_equals($sec, (string) $req->get_header('x_telegram_bot_api_secret_token'))) return new WP_REST_Response(['ok' => false], 403);
    $u = $req->get_json_params(); if (!is_array($u)) return ['ok' => true];

    if (!empty($u['callback_query'])) {
        $q = $u['callback_query']; $from = $q['from'] ?? []; $id = (string) ($from['id'] ?? '');
        $chat = $q['message']['chat']['id'] ?? 0; $mid = $q['message']['message_id'] ?? 0; $data = (string) ($q['data'] ?? '');
        $role = eh_tgb_role($id);
        if (!$role) { eh_tgb_api('answerCallbackQuery', ['callback_query_id' => $q['id'], 'text' => 'आपको अनुमति नहीं है।', 'show_alert' => true]); return ['ok' => true]; }
        $text = ''; $kb = null; $note = '';
        if ($data === 'menu') { $text = eh_tgb_menu_text(); $kb = eh_tgb_menu_kb($role); }
        elseif (strpos($data, 'r:') === 0) { $text = eh_tgb_stats(substr($data, 2)); $kb = eh_tgb_back_kb($data); }
        elseif ($data === 'live') { $text = eh_tgb_live(); $kb = eh_tgb_back_kb('live'); }
        elseif ($data === 'top') { $text = eh_tgb_top(); $kb = eh_tgb_back_kb('top'); }
        elseif ($data === 'p3284') { $text = eh_tgb_3284(); $kb = eh_tgb_back_kb('p3284'); }
        elseif ($data === 'users') { $text = eh_tgb_users(); $kb = eh_tgb_back_kb('users'); }
        elseif ($data === 'admins') { [$text, $kb] = eh_tgb_admins_view($role); }
        elseif ($data === 'add' && $role === 'owner') {
            $code = (string) wp_rand(100000, 999999);
            update_option('eh_tgb_join', ['c' => $code, 'x' => time() + 600], false);
            $text = '➕ <b>नया admin जोड़ें</b>' . "\n\nयह code उस आदमी को भेजिए। वह इस बॉट में यह लिखे:\n\n<code>/join " . $code . "</code>\n\nCode 10 मिनट चलेगा और एक ही बार काम करेगा।";
            $kb = eh_tgb_back_kb('menu');
        } elseif ($data === 'pa' && $role === 'owner') {
            eh_tgb_pstate($id, ['s' => 'lang']);
            $text = '📝 <b>नया पैसेज</b>' . "\n\nभाषा चुनिए:"; $kb = ['inline_keyboard' => [[['text' => '🇬🇧 English', 'callback_data' => 'pa:l:en'], ['text' => '🇮🇳 हिंदी', 'callback_data' => 'pa:l:hi']], [['text' => '❌ रद्द', 'callback_data' => 'pa:x']]]];
        } elseif (strpos($data, 'pa:l:') === 0 && $role === 'owner') {
            $st = eh_tgb_pstate($id) ?: []; $st['lang'] = substr($data, 5) === 'hi' ? 'hi' : 'en'; $st['s'] = 'level'; eh_tgb_pstate($id, $st);
            $text = '📝 भाषा: <b>' . ($st['lang'] === 'hi' ? 'हिंदी' : 'English') . '</b>' . "\n\nस्तर चुनिए:";
            $kb = ['inline_keyboard' => [[['text' => '🟢 Easy', 'callback_data' => 'pa:v:E'], ['text' => '🟡 Moderate', 'callback_data' => 'pa:v:M']], [['text' => '🔴 Hard', 'callback_data' => 'pa:v:H'], ['text' => '🟣 Super Hard', 'callback_data' => 'pa:v:S']], [['text' => '❌ रद्द', 'callback_data' => 'pa:x']]]];
        } elseif (strpos($data, 'pa:v:') === 0 && $role === 'owner') {
            $st = eh_tgb_pstate($id) ?: []; $v = substr($data, 5); $st['level'] = in_array($v, ['E', 'M', 'H', 'S'], true) ? $v : 'M'; $st['s'] = 'title'; eh_tgb_pstate($id, $st);
            $text = '📝 ' . ($st['lang'] === 'hi' ? 'हिंदी' : 'English') . ' · <b>' . eh_tgb_lvname($st['level']) . '</b>' . "\n\nअब पैसेज का <b>शीर्षक</b> लिखकर भेजिए।"; $kb = ['inline_keyboard' => [[['text' => '❌ रद्द', 'callback_data' => 'pa:x']]]];
        } elseif ($data === 'pa:x' && $role === 'owner') {
            eh_tgb_pstate($id, false); $text = '❌ रद्द कर दिया।' . "\n\n" . eh_tgb_menu_text(); $kb = eh_tgb_menu_kb($role);
        } elseif ($data === 'pa:ok' && $role === 'owner') {
            $st = eh_tgb_pstate($id);
            if (!$st || empty($st['text'])) { $text = 'कुछ नहीं मिला, फिर से शुरू कीजिए।'; $kb = eh_tgb_menu_kb($role); }
            else {
                $bot = get_option('eh_bot_passages', []); if (!is_array($bot)) $bot = [];
                $pid = 'bt' . strtolower(base_convert((string) time(), 10, 36)) . $st['lang'];
                $bot[] = ['id' => $pid, 'lang' => $st['lang'], 'title' => $st['title'], 'text' => $st['text'], 'words' => count(preg_split('/\s+/u', $st['text'])), 'chars' => mb_strlen($st['text']),
                    'level' => $st['level'], 'day' => '', 'added' => wp_date('Y-m-d H:i:s'), 'bot' => true, 'tag' => 'New'];
                update_option('eh_bot_passages', $bot, false); eh_tgb_pstate($id, false); eh_tgb_purge();
                $text = '✅ <b>पैसेज वेबसाइट पर डाल दिया</b>' . "\n\n<b>" . esc_html($st['title']) . '</b> · ' . ($st['lang'] === 'hi' ? 'हिंदी' : 'English') . ' · ' . eh_tgb_lvname($st['level']) . "\n\nअब यह Live Typing, 3284 सेक्शन और All Exams (सभी " . ($st['lang'] === 'hi' ? 'हिंदी' : 'English') . " exams) में सबसे ऊपर है।\n🔗 " . esc_url(home_url('/live-typing/'));
                $kb = ['inline_keyboard' => [[['text' => '📝 एक और', 'callback_data' => 'pa'], ['text' => '📋 मेरे पैसेज', 'callback_data' => 'pl']], [['text' => '☰ Menu', 'callback_data' => 'menu']]]];
            }
        } elseif ($data === 'pl' && $role === 'owner') {
            [$text, $kb] = eh_tgb_plist_view();
        } elseif (strpos($data, 'pd:') === 0 && $role === 'owner') {
            $did = substr($data, 3); $text = '⚠️ यह पैसेज वेबसाइट से हटाना है?'; $kb = ['inline_keyboard' => [[['text' => '✅ हाँ, हटाओ', 'callback_data' => 'pdy:' . $did], ['text' => '↩ नहीं', 'callback_data' => 'pl']]]];
        } elseif (strpos($data, 'pdy:') === 0 && $role === 'owner') {
            $did = substr($data, 4); $bot = get_option('eh_bot_passages', []); if (!is_array($bot)) $bot = [];
            $bot = array_values(array_filter($bot, function ($p) use ($did) { return ($p['id'] ?? '') !== $did; })); update_option('eh_bot_passages', $bot, false); eh_tgb_purge();
            $note = 'हटा दिया'; [$text, $kb] = eh_tgb_plist_view();
        } elseif ($data === 'upd' && $role === 'owner') {
            $cur = substr((string) get_option('eh_deployed_sha', '-'), 0, 7);
            $api = wp_remote_get('https://api.github.com/repos/kdthobbit-eng/exam-hub-files/commits/main', ['timeout' => 15, 'headers' => ['User-Agent' => 'exam-hub-updater', 'Accept' => 'application/vnd.github+json']]);
            $j = json_decode(wp_remote_retrieve_body($api), true); $new = is_array($j) && !empty($j['sha']) ? substr($j['sha'], 0, 7) : '?';
            $msg = is_array($j) && !empty($j['commit']['message']) ? strtok($j['commit']['message'], "\n") : '';
            $text = '🔄 <b>Theme Update</b>' . "\n\nअभी लगी version: <code>" . esc_html($cur) . "</code>\nGitHub पर नई: <code>" . esc_html($new) . '</code>' . ($msg ? "\n<i>" . esc_html(mb_substr($msg, 0, 120)) . '</i>' : '') . "\n\n" . ($cur === $new ? '✅ वेबसाइट पहले से नई version पर है।' : 'Update करना है?');
            $kb = ['inline_keyboard' => [[['text' => '✅ हाँ, Update करो', 'callback_data' => 'upd:go']], [['text' => '☰ Menu', 'callback_data' => 'menu']]]];
        } elseif ($data === 'upd:go' && $role === 'owner') {
            if (get_transient('eh_tgb_upd_lock')) { $note = 'Update पहले से चल रहा है…'; }
            else {
                set_transient('eh_tgb_upd_lock', 1, 180);
                eh_tgb_api('answerCallbackQuery', ['callback_query_id' => $q['id'], 'text' => 'Update शुरू…']); $q['id'] = '';
                $r = function_exists('eh_self_update') ? eh_self_update() : 'Updater नहीं मिला';
                delete_transient('eh_tgb_upd_lock');
                $text = (strpos($r, 'OK') === 0 ? '✅ <b>Theme update हो गई</b>' . "\nVersion: <code>" . esc_html(substr($r, 3)) . '</code>' : '❌ <b>Update नहीं हुआ</b>' . "\n" . esc_html($r));
                $kb = eh_tgb_back_kb('upd');
            }
        } elseif (strpos($data, 'del:') === 0 && $role === 'owner') {
            $a = eh_tgb_admins(); $did = substr($data, 4);
            if (isset($a[$did]) && $a[$did]['r'] !== 'owner') { $note = $a[$did]['n'] . ' हटा दिए गए'; unset($a[$did]); update_option('eh_tgb_admins', $a, false); }
            [$text, $kb] = eh_tgb_admins_view($role);
        }
        if ($q['id'] !== '') eh_tgb_api('answerCallbackQuery', ['callback_query_id' => $q['id'], 'text' => $note]);
        if ($text !== '') eh_tgb_api('editMessageText', ['chat_id' => $chat, 'message_id' => $mid, 'text' => $text . "\n\n<i>" . esc_html(wp_date('d M, h:i:s A')) . '</i>', 'parse_mode' => 'HTML', 'reply_markup' => $kb]);
        return ['ok' => true];
    }

    $m = $u['message'] ?? null; if (!$m || empty($m['text'])) return ['ok' => true];
    $chat = $m['chat']['id']; $from = $m['from'] ?? []; $id = (string) ($from['id'] ?? '');
    if (($m['chat']['type'] ?? '') !== 'private') return ['ok' => true];
    $name = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')) ?: ('@' . ($from['username'] ?? $id));
    $txt = trim($m['text']); $a = eh_tgb_admins();
    $send = function ($t, $kb = null) use ($chat) { $args = ['chat_id' => $chat, 'text' => $t, 'parse_mode' => 'HTML']; if ($kb) $args['reply_markup'] = $kb; eh_tgb_api('sendMessage', $args); };

    if (preg_match('~^/join\s+(\d{6})~', $txt, $mm)) {
        $j = get_option('eh_tgb_join', []);
        if (!empty($j['c']) && hash_equals($j['c'], $mm[1]) && time() < (int) $j['x']) {
            delete_option('eh_tgb_join');
            if (!isset($a[$id])) { $a[$id] = ['n' => $name, 'r' => 'admin']; update_option('eh_tgb_admins', $a, false); }
            foreach ($a as $oid => $o) if ($o['r'] === 'owner' && $oid !== $id) eh_tgb_api('sendMessage', ['chat_id' => $oid, 'text' => '✅ ' . esc_html($name) . ' admin बन गए।', 'parse_mode' => 'HTML']);
            $send('✅ आप admin बन गए।' . "\n\n" . eh_tgb_menu_text(), eh_tgb_menu_kb('admin'));
        } else $send('❌ Code गलत है या उसका समय खत्म हो गया। Owner से नया code माँगिए।');
        return ['ok' => true];
    }
    if (!$a && strpos($txt, '/start') === 0) {
        $a[$id] = ['n' => $name, 'r' => 'owner']; update_option('eh_tgb_admins', $a, false);
        $send('👑 आप इस बॉट के <b>Owner</b> बन गए।' . "\n\n" . eh_tgb_menu_text(), eh_tgb_menu_kb('owner'));
        return ['ok' => true];
    }
    $role = eh_tgb_role($id);
    if (!$role) { $send('⛔ आपको इस बॉट की अनुमति नहीं है।' . "\nआपकी Telegram ID: <code>" . esc_html($id) . '</code>'); return ['ok' => true]; }
    $pst = $role === 'owner' && strpos($txt, '/') !== 0 ? eh_tgb_pstate($id) : null;
    if ($pst && time() - (int) ($pst['t'] ?? 0) < 3600 && in_array($pst['s'] ?? '', ['title', 'text'], true)) {
        $cx = ['inline_keyboard' => [[['text' => '❌ रद्द', 'callback_data' => 'pa:x']]]];
        if ($pst['s'] === 'title') {
            $pst['title'] = mb_substr(eh_tgb_pclean($txt), 0, 90); $pst['s'] = 'text'; eh_tgb_pstate($id, $pst);
            $send('📝 शीर्षक: <b>' . esc_html($pst['title']) . '</b>' . "\n\nअब पूरा <b>पैसेज paste</b> करके भेजिए।", $cx);
        } else {
            $p = eh_tgb_pclean($txt); $w = count(preg_split('/\s+/u', $p));
            if ($w < 40) { $send('⚠️ पैसेज बहुत छोटा है (' . $w . ' शब्द)। कम से कम 40 शब्द भेजिए।', $cx); return ['ok' => true]; }
            $dup = false; $k = mb_substr($p, 0, 60); foreach (eh_passages() as $q2) if (mb_substr($q2['text'], 0, 60) === $k) { $dup = true; break; }
            if ($dup) { $send('⚠️ यह पैसेज पहले से वेबसाइट पर है। दूसरा भेजिए।', $cx); return ['ok' => true]; }
            $pst['text'] = $p; $pst['s'] = 'confirm'; eh_tgb_pstate($id, $pst);
            $warn = eh_tgb_pwarn($pst['lang'], $p);
            $send('👀 <b>झलक</b>' . "\n\n<b>" . esc_html($pst['title']) . '</b> · ' . ($pst['lang'] === 'hi' ? 'हिंदी' : 'English') . ' · ' . eh_tgb_lvname($pst['level']) . "\n" . $w . ' शब्द · ' . mb_strlen($p) . " key strokes\n\n<i>" . esc_html(mb_substr($p, 0, 220)) . '…</i>' . ($warn ? "\n\n⚠️ " . esc_html(implode(' · ', $warn)) : '') . "\n\nवेबसाइट पर डालें?",
                ['inline_keyboard' => [[['text' => '✅ हाँ, डालो', 'callback_data' => 'pa:ok'], ['text' => '❌ रद्द', 'callback_data' => 'pa:x']]]]);
        }
        return ['ok' => true];
    }
    if ($txt === '/today') $send(eh_tgb_stats('today'), eh_tgb_back_kb('r:today'));
    elseif ($txt === '/hour') $send(eh_tgb_stats('hour'), eh_tgb_back_kb('r:hour'));
    elseif ($txt === '/all') $send(eh_tgb_stats('all'), eh_tgb_back_kb('r:all'));
    elseif ($txt === '/3284') $send(eh_tgb_3284(), eh_tgb_back_kb('p3284'));
    elseif ($txt === '/update' && $role === 'owner') $send('🔄 Theme update के लिए नीचे बटन दबाइए।', ['inline_keyboard' => [[['text' => '🔄 Update theme', 'callback_data' => 'upd']]]]);
    else $send(eh_tgb_menu_text(), eh_tgb_menu_kb($role));
    return ['ok' => true];
}

/* ---------- WP admin page ---------- */
add_action('admin_menu', function () {
    add_submenu_page('eh-settings', 'Telegram Bot', 'Telegram Bot', 'manage_options', 'eh-tgbot', 'eh_tgb_page');
}, 21);
add_action('admin_post_eh_tgb_save', function () {
    if (!current_user_can('manage_options')) wp_die('No');
    check_admin_referer('eh_tgb');
    $do = sanitize_key($_POST['do'] ?? '');
    if ($do === 'connect') {
        $tok = trim(sanitize_text_field(wp_unslash($_POST['token'] ?? '')));
        if ($tok !== '') update_option('eh_tgb_token', $tok, false);
        if (!get_option('eh_tgb_secret')) update_option('eh_tgb_secret', wp_generate_password(40, false), false);
        if (!get_option('eh_tgb_since')) update_option('eh_tgb_since', current_time('mysql'), false);
        $r = eh_tgb_api('setWebhook', ['url' => rest_url('eh/v1/tgbot'), 'secret_token' => get_option('eh_tgb_secret'), 'allowed_updates' => ['message', 'callback_query'], 'drop_pending_updates' => true]);
        eh_tgb_api('setMyCommands', ['commands' => [['command' => 'start', 'description' => 'Menu'], ['command' => 'today', 'description' => 'आज के आँकड़े'], ['command' => 'hour', 'description' => 'पिछला 1 घंटा'], ['command' => 'all', 'description' => 'शुरू से अब तक'], ['command' => '3284', 'description' => '3284 Pattern Passage'], ['command' => 'update', 'description' => 'Theme update (owner)']]]);
        $msg = !empty($r['ok']) ? 'connected' : 'fail';
    } elseif ($do === 'ids') {
        update_option('eh_tgb_ids', sanitize_textarea_field(wp_unslash($_POST['ids'] ?? '')), false); $msg = 'saved';
    } elseif ($do === 'disconnect') {
        eh_tgb_api('deleteWebhook', []); delete_option('eh_tgb_token'); $msg = 'off';
    } elseif ($do === 'reset') {
        delete_option('eh_tgb_admins'); $msg = 'reset';
    } else $msg = '';
    wp_safe_redirect(add_query_arg(['page' => 'eh-tgbot', 'm' => $msg], admin_url('admin.php'))); exit;
});
function eh_tgb_page() {
    if (!current_user_can('manage_options')) return;
    $tok = eh_tgb_token(); $me = $tok ? eh_tgb_api('getMe') : null; $wh = $tok ? eh_tgb_api('getWebhookInfo') : null;
    $ok = !empty($me['ok']) && !empty($wh['result']['url']);
    $msgs = ['connected' => ['success', 'Bot connected. अब Telegram में अपने बॉट को /start भेजिए।'], 'fail' => ['error', 'Connect नहीं हुआ — token दोबारा जाँचिए।'], 'saved' => ['success', 'IDs saved.'], 'off' => ['warning', 'Bot disconnected.'], 'reset' => ['warning', 'Admin list खाली कर दी गई। अगला /start भेजने वाला Owner बनेगा।']];
    $m = $msgs[$_GET['m'] ?? ''] ?? null;
    $f = function ($do, $inner, $btn, $cls = 'button-primary', $confirm = '') {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin:10px 0"' . ($confirm ? ' onsubmit="return confirm(\'' . esc_js($confirm) . '\')"' : '') . '>';
        wp_nonce_field('eh_tgb'); echo '<input type="hidden" name="action" value="eh_tgb_save"><input type="hidden" name="do" value="' . esc_attr($do) . '">' . $inner . ' <button class="button ' . $cls . '">' . esc_html($btn) . '</button></form>';
    };
    echo '<div class="wrap"><h1>Telegram Stats Bot</h1>';
    if ($m) echo '<div class="notice notice-' . $m[0] . '"><p>' . esc_html($m[1]) . '</p></div>';
    echo '<div class="card" style="max-width:760px"><h2>1. Bot connect</h2>';
    if ($ok) echo '<p>✅ Connected: <b>@' . esc_html($me['result']['username']) . '</b> — <a href="https://t.me/' . esc_attr($me['result']['username']) . '" target="_blank" rel="noopener">बॉट खोलें</a></p>';
    else echo '<p>Telegram में <b>@BotFather</b> → /newbot से बॉट बनाइए, फिर उसका token यहाँ डालिए।</p>';
    $f('connect', '<input type="password" name="token" class="regular-text" placeholder="' . ($tok ? 'Token saved — नया डालना हो तो यहाँ' : '123456:ABC-...') . '" autocomplete="off">', $ok ? 'Reconnect' : 'Connect');
    if ($tok) $f('disconnect', '', 'Disconnect', 'button-secondary', 'Bot disconnect करें?');
    echo '</div><div class="card" style="max-width:760px"><h2>2. Admins</h2><p>पहला /start भेजने वाला <b>Owner</b> बनता है। Owner बॉट में "➕ Add admin" दबाकर code बनाता है; दूसरा आदमी <code>/join CODE</code> भेजकर जुड़ जाता है।</p><ul>';
    $a = eh_tgb_admins();
    if (!$a) echo '<li><i>अभी कोई admin नहीं — बॉट को /start भेजिए।</i></li>';
    foreach ($a as $id => $x) echo '<li>' . ($x['r'] === 'owner' ? '👑 ' : '• ') . esc_html($x['n']) . ' — <code>' . esc_html($id) . '</code></li>';
    echo '</ul>';
    if ($a) $f('reset', '', 'Admin list खाली करें', 'button-secondary', 'सभी admins हट जाएँगे। आगे बढ़ें?');
    echo '<h3>Allowed Telegram IDs (वैकल्पिक)</h3><p>यहाँ डाली गई ID वाले लोग भी बॉट देख सकेंगे (हर लाइन में एक ID)। ID पता करने के लिए वह आदमी बॉट को कोई भी मैसेज भेजे — बॉट उसकी ID बता देगा।</p>';
    $f('ids', '<textarea name="ids" rows="4" class="large-text code">' . esc_textarea((string) get_option('eh_tgb_ids', '')) . '</textarea><br>', 'Save IDs');
    echo '</div></div>';
}
