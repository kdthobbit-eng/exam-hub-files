<?php
/* Super TET live mock: rounds, attempts, server-side timer, scoring, rank, percentile, admin. */
if (!defined('ABSPATH')) exit;

define('EH_STM_DB', '2');
define('EH_STM_SECS', 7200);

function eh_stm_table() { global $wpdb; return $wpdb->prefix . 'eh_mock'; }

/* Rounds: times are IST. 't1' = team trial, 'm1' = the real live mock on 22 Nov. */
function eh_stm_rounds() {
    $tz = new DateTimeZone('Asia/Kolkata');
    $ts = function ($s) use ($tz) { return (new DateTime($s, $tz))->getTimestamp(); };
    $o = get_option('eh_stm_rounds');
    $list = is_array($o) && $o ? $o : [
        ['id' => 't1', 'name' => 'Super TET Mock 2026 — ट्रायल', 'login' => '2026-11-21 21:00', 'start' => '2026-11-21 21:00', 'end' => '2026-11-22 09:30', 'paper' => 'supertet-mock1'],
        ['id' => 'm1', 'name' => 'Super TET Mock 2026 — लाइव', 'login' => '2026-11-22 09:30', 'start' => '2026-11-22 10:00', 'end' => '2026-11-22 23:59', 'paper' => 'supertet-mock1'],
    ];
    $ctl = get_option('eh_stm_ctl', []); if (!is_array($ctl)) $ctl = [];
    return array_map(function ($r) use ($ts, $ctl) {
        $r['login_ts'] = $ts($r['login']); $r['start_ts'] = $ts($r['start']); $r['end_ts'] = $ts($r['end']);
        $c = $ctl[$r['id']] ?? [];
        if (!empty($c['start'])) { $r['start_ts'] = min($r['start_ts'], (int) $c['start']); $r['login_ts'] = min($r['login_ts'], $r['start_ts']); }
        if (!empty($c['end'])) $r['end_ts'] = min($r['end_ts'], (int) $c['end']);
        return $r;
    }, $list);
}
/* Current round = first round whose end has not passed, else the last one. */
function eh_stm_round($id = '') {
    $all = eh_stm_rounds(); $now = time();
    if ($id) { foreach ($all as $r) if ($r['id'] === $id) return $r; }
    foreach ($all as $r) if ($now < $r['end_ts']) return $r;
    return end($all);
}
function eh_stm_paper($r) {
    static $c = [];
    $k = preg_replace('/[^a-z0-9-]/', '', $r['paper']);
    if (!isset($c[$k])) { $c[$k] = json_decode((string) @file_get_contents(EH_DIR . '/data/' . $k . '.json'), true) ?: []; }
    return $c[$k];
}

add_action('init', function () {
    if (get_option('eh_stm_db') === EH_STM_DB) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $t = eh_stm_table(); $c = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $t (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        round varchar(12) NOT NULL DEFAULT '',
        uid bigint(20) unsigned NOT NULL DEFAULT 0,
        att smallint(5) NOT NULL DEFAULT 1,
        counted tinyint(1) NOT NULL DEFAULT 0,
        name varchar(80) NOT NULL DEFAULT '',
        email varchar(120) NOT NULL DEFAULT '',
        seen int(10) unsigned NOT NULL DEFAULT 0,
        started int(10) unsigned NOT NULL DEFAULT 0,
        deadline int(10) unsigned NOT NULL DEFAULT 0,
        last int(10) unsigned NOT NULL DEFAULT 0,
        submitted int(10) unsigned NOT NULL DEFAULT 0,
        cur smallint(5) NOT NULL DEFAULT 0,
        answered smallint(5) NOT NULL DEFAULT 0,
        tabs smallint(5) NOT NULL DEFAULT 0,
        correct smallint(5) NOT NULL DEFAULT 0,
        wrong smallint(5) NOT NULL DEFAULT 0,
        score smallint(6) NOT NULL DEFAULT 0,
        dev varchar(10) NOT NULL DEFAULT '',
        ans text NULL,
        st text NULL,
        tq text NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY rua (round,uid,att),
        KEY round (round)
    ) $c;");
    if ($wpdb->get_var("SHOW INDEX FROM $t WHERE Key_name = 'ru'")) $wpdb->query("ALTER TABLE $t DROP INDEX ru");
    $wpdb->query("UPDATE $t SET counted = 1 WHERE submitted > 0 AND att = 1");
    update_option('eh_stm_db', EH_STM_DB);
});

function eh_stm_row($round, $uid) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . eh_stm_table() . ' WHERE round = %s AND uid = %d ORDER BY att DESC LIMIT 1', $round, $uid), ARRAY_A);
}
function eh_stm_touch($r) {
    global $wpdb;
    $u = wp_get_current_user(); $t = eh_stm_table();
    $row = eh_stm_row($r['id'], $u->ID);
    if (!$row) {
        $wpdb->insert($t, ['round' => $r['id'], 'uid' => $u->ID, 'name' => mb_substr($u->display_name ?: $u->user_login, 0, 80), 'email' => mb_substr($u->user_email, 0, 120), 'seen' => time(), 'last' => time(), 'dev' => wp_is_mobile() ? 'mobile' : 'pc', 'ans' => '{}', 'st' => '{}', 'tq' => '{}']);
        $row = eh_stm_row($r['id'], $u->ID);
    }
    return $row;
}
function eh_stm_clean_map($v, $re) {
    $out = [];
    if (!is_array($v)) return $out;
    foreach ($v as $k => $x) { $k = (int) $k; if ($k < 0 || $k > 399) continue; if (preg_match($re, (string) $x)) $out[$k] = (string) $x; }
    return $out;
}
function eh_stm_score($r, $ans) {
    $Q = eh_stm_paper($r); $c = 0; $w = 0;
    foreach ($ans as $i => $a) { if (!isset($Q[$i])) continue; if ($a === $Q[$i]['a']) $c++; else $w++; }
    return [$c, $w, $c * 3 - $w];
}
function eh_stm_finish_row($r, $row, $when) {
    global $wpdb;
    $ans = json_decode($row['ans'] ?: '{}', true) ?: [];
    [$c, $w, $s] = eh_stm_score($r, $ans);
    $wpdb->query($wpdb->prepare('UPDATE ' . eh_stm_table() . ' SET counted = 0 WHERE round = %s AND uid = %d AND att < %d', $row['round'], $row['uid'], $row['att']));
    $wpdb->update(eh_stm_table(), ['submitted' => $when, 'correct' => $c, 'wrong' => $w, 'score' => $s, 'counted' => 1], ['id' => $row['id']]);
}
/* Attempts whose time ran out without a submit are submitted at their deadline. */
function eh_stm_expire($r) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . eh_stm_table() . ' WHERE round = %s AND started > 0 AND submitted = 0 AND deadline < %d', $r['id'], time() - 20), ARRAY_A);
    foreach ($rows as $row) eh_stm_finish_row($r, $row, (int) $row['deadline']);
}
function eh_stm_public_q($r) {
    return array_map(function ($q) { $o = ['q' => $q['q'], 'o' => $q['o']]; if (!empty($q['p'])) $o['p'] = $q['p']; if (!empty($q['qe'])) { $o['qe'] = $q['qe']; $o['oe'] = $q['oe']; } return $o; }, eh_stm_paper($r));
}
function eh_stm_status($r) {
    $n = time();
    if ($n < $r['login_ts']) return 'soon';
    if ($n < $r['start_ts']) return 'login';
    if ($n < $r['end_ts']) return 'live';
    return 'over';
}
function eh_stm_roll($uid) { return 'XL26' . str_pad((string) $uid, 6, '0', STR_PAD_LEFT); }
function eh_stm_round_out($r) {
    return ['id' => $r['id'], 'name' => $r['name'], 'login' => $r['login_ts'], 'start' => $r['start_ts'], 'end' => $r['end_ts'], 'status' => eh_stm_status($r)];
}
function eh_stm_attempt_out($row, $r) {
    $o = ['started' => (int) $row['started'], 'deadline' => (int) $row['deadline'], 'submitted' => (int) $row['submitted'],
          'cur' => (int) $row['cur'], 'tabs' => (int) $row['tabs'], 'last' => (int) $row['last'], 'att' => (int) $row['att'],
          'ans' => (object) (json_decode($row['ans'] ?: '{}', true) ?: []), 'st' => (object) (json_decode($row['st'] ?: '{}', true) ?: []), 'tq' => (object) (json_decode($row['tq'] ?: '{}', true) ?: [])];
    if ($row['started'] && !$row['submitted']) $o['q'] = eh_stm_public_q($r);
    return $o;
}
function eh_stm_result($r, $row) {
    eh_stm_expire($r);
    $row = eh_stm_row($r['id'], $row['uid']);
    $Q = eh_stm_paper($r);
    return array_merge([
        'ok' => true, 'name' => $row['name'], 'email' => $row['email'], 'roll' => eh_stm_roll((int) $row['uid']),
        'score' => (int) $row['score'], 'correct' => (int) $row['correct'], 'wrong' => (int) $row['wrong'],
        'started' => (int) $row['started'], 'used' => max(0, (int) $row['submitted'] - (int) $row['started']), 'tabs' => (int) $row['tabs'],
        'ans' => (object) (json_decode($row['ans'] ?: '{}', true) ?: []), 'tq' => (object) (json_decode($row['tq'] ?: '{}', true) ?: []),
        'key' => array_map(function ($q) { return $q['a']; }, $Q), 'exp' => array_map(function ($q) { return $q['e'] ?? ''; }, $Q),
        'q' => eh_stm_public_q($r), 'att' => (int) $row['att'],
        'hist' => array_map(function ($x) { return [(int) $x['att'], (int) $x['score'], (int) $x['submitted']]; }, $GLOBALS['wpdb']->get_results($GLOBALS['wpdb']->prepare('SELECT att, score, submitted FROM ' . eh_stm_table() . ' WHERE round = %s AND uid = %d AND submitted > 0 ORDER BY att', $r['id'], $row['uid']), ARRAY_A)),
    ], eh_stm_live($r, (int) $row['score']));
}
function eh_stm_live($r, $score) {
    global $wpdb; $t = eh_stm_table();
    eh_stm_expire($r);
    $base = $wpdb->prepare("FROM $t WHERE round = %s AND submitted > 0 AND counted = 1", $r['id']);
    $n = (int) $wpdb->get_var("SELECT COUNT(*) $base");
    $above = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) $base AND score > %d", $score));
    $le = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) $base AND score <= %d", $score));
    $agg = $wpdb->get_row("SELECT MAX(score) mx, AVG(score) av $base", ARRAY_A);
    $d = $wpdb->get_results("SELECT score s, COUNT(*) c $base GROUP BY score", ARRAY_A);
    $top = $wpdb->get_results("SELECT name, score $base ORDER BY score DESC, (submitted - started) ASC LIMIT 10", ARRAY_A);
    $live = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT uid) FROM $t WHERE round = %s AND started > 0 AND submitted = 0 AND last > %d", $r['id'], time() - 120));
    return ['total' => $n, 'rank' => $above + 1, 'percentile' => $n ? round($le * 100 / $n, 2) : 100,
        'topper' => (int) ($agg['mx'] ?? 0), 'avg' => round((float) ($agg['av'] ?? 0), 1), 'writing' => $live, 'final' => time() >= $r['end_ts'],
        'dist' => array_map(function ($x) { return [(int) $x['s'], (int) $x['c']]; }, $d),
        'top' => array_map(function ($x) { return ['n' => eh_stm_short($x['name']), 's' => (int) $x['score']]; }, $top)];
}
function eh_stm_short($n) { $p = preg_split('/\s+/', trim($n)); return $p[0] . (isset($p[1]) ? ' ' . mb_substr($p[1], 0, 1) . '.' : ''); }

add_action('rest_api_init', function () {
    $auth = function () { return is_user_logged_in(); };
    $admin = function () { return current_user_can('manage_options'); };
    register_rest_route('eh/v1', '/stm/state', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_round(); $row = eh_stm_touch($r);
        global $wpdb; $wpdb->update(eh_stm_table(), ['last' => time()], ['id' => $row['id']]);
        if ($row['started'] && !$row['submitted'] && time() > (int) $row['deadline'] + 20) { eh_stm_finish_row($r, $row, (int) $row['deadline']); $row = eh_stm_row($r['id'], $row['uid']); }
        return ['now' => time(), 'round' => eh_stm_round_out($r), 'roll' => eh_stm_roll((int) $row['uid']), 'name' => $row['name'], 'attempt' => eh_stm_attempt_out($row, $r)];
    }]);
    register_rest_route('eh/v1', '/stm/start', ['methods' => 'POST', 'permission_callback' => $auth, 'callback' => function (WP_REST_Request $q) {
        global $wpdb;
        $r = eh_stm_round(); $row = eh_stm_touch($r); $now = time();
        if (eh_stm_status($r) !== 'live') return new WP_Error('closed', 'परीक्षा अभी खुली नहीं है', ['status' => 403]);
        if ($row['submitted'] && !empty($q['retry'])) {
            $u = wp_get_current_user();
            $wpdb->insert(eh_stm_table(), ['round' => $r['id'], 'uid' => $u->ID, 'att' => (int) $row['att'] + 1, 'name' => $row['name'], 'email' => $row['email'], 'seen' => $now, 'last' => $now, 'ans' => '{}', 'st' => '{}', 'tq' => '{}']);
            $row = eh_stm_row($r['id'], $u->ID);
        }
        if (!$row['started']) {
            $wpdb->update(eh_stm_table(), ['started' => $now, 'deadline' => min($now + EH_STM_SECS, $r['end_ts']), 'last' => $now, 'dev' => wp_is_mobile() ? 'mobile' : 'pc'], ['id' => $row['id']]);
            $row = eh_stm_row($r['id'], $row['uid']);
        }
        return ['now' => $now, 'attempt' => eh_stm_attempt_out($row, $r)];
    }]);
    register_rest_route('eh/v1', '/stm/save', ['methods' => 'POST', 'permission_callback' => $auth, 'callback' => function (WP_REST_Request $q) {
        global $wpdb;
        $r = eh_stm_round(); $row = eh_stm_row($r['id'], get_current_user_id()); $now = time();
        if (!$row || !$row['started']) return new WP_Error('no', 'no attempt', ['status' => 400]);
        if ($row['submitted']) return ['ok' => true, 'submitted' => true];
        if ($now > (int) $row['deadline'] + 20) { eh_stm_finish_row($r, $row, (int) $row['deadline']); return ['ok' => true, 'submitted' => true]; }
        $ans = eh_stm_clean_map($q['ans'], '/^[ABCD]$/');
        $st = eh_stm_clean_map($q['st'], '/^(na|an|mr|am)$/');
        $tq = array_map('intval', eh_stm_clean_map($q['tq'], '/^\d{1,5}$/'));
        $wpdb->update(eh_stm_table(), ['ans' => wp_json_encode((object) $ans), 'st' => wp_json_encode((object) $st), 'tq' => wp_json_encode((object) $tq),
            'answered' => count($ans), 'cur' => max(0, min(399, (int) $q['cur'])), 'tabs' => max((int) $row['tabs'], min(999, (int) $q['tabs'])), 'last' => $now], ['id' => $row['id']]);
        if (!empty($q['submit'])) { $row = eh_stm_row($r['id'], $row['uid']); eh_stm_finish_row($r, $row, min($now, (int) $row['deadline'])); return ['ok' => true, 'submitted' => true]; }
        return ['ok' => true, 'now' => $now];
    }]);
    register_rest_route('eh/v1', '/stm/result', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_round(); $row = eh_stm_row($r['id'], get_current_user_id());
        if (!$row || !$row['started']) return new WP_Error('no', 'no attempt', ['status' => 404]);
        if (!$row['submitted']) { if (time() <= (int) $row['deadline'] + 20) return new WP_Error('run', 'running', ['status' => 409]); eh_stm_finish_row($r, $row, (int) $row['deadline']); }
        return eh_stm_result($r, $row);
    }]);
    register_rest_route('eh/v1', '/stm/live', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_round(); $row = eh_stm_row($r['id'], get_current_user_id());
        if (!$row || !$row['submitted']) return new WP_Error('no', 'no result', ['status' => 404]);
        return eh_stm_live($r, (int) $row['score']);
    }]);
    register_rest_route('eh/v1', '/stm/paper', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_round(); $row = eh_stm_row($r['id'], get_current_user_id());
        if (time() < $r['end_ts'] && !($row && $row['submitted'])) return new WP_Error('no', 'परीक्षा समाप्त होने के बाद', ['status' => 403]);
        return ['q' => eh_stm_public_q($r), 'key' => array_map(function ($q) { return $q['a']; }, eh_stm_paper($r)), 'exp' => array_map(function ($q) { return $q['e'] ?? ''; }, eh_stm_paper($r))];
    }]);
    register_rest_route('eh/v1', '/stm/admin', ['methods' => 'GET', 'permission_callback' => $admin, 'callback' => function (WP_REST_Request $q) {
        global $wpdb;
        $r = eh_stm_round(sanitize_key($q['round'] ?? '')); eh_stm_expire($r);
        $rows = $wpdb->get_results($wpdb->prepare('SELECT id, uid, att, counted, name, email, seen, started, deadline, last, submitted, answered, tabs, correct, wrong, score, dev FROM ' . eh_stm_table() . ' WHERE round = %s ORDER BY submitted > 0, score DESC, started DESC, seen DESC', $r['id']), ARRAY_A);
        return ['now' => time(), 'round' => eh_stm_round_out($r), 'rounds' => array_map('eh_stm_round_out', eh_stm_rounds()), 'rows' => $rows];
    }]);
    register_rest_route('eh/v1', '/stm/reset', ['methods' => 'POST', 'permission_callback' => $admin, 'callback' => function (WP_REST_Request $q) {
        global $wpdb; $wpdb->delete(eh_stm_table(), ['id' => (int) $q['id']]); return ['ok' => true];
    }]);
});

/* Numbers for the Telegram bot /mock command. */
function eh_stm_counts($r = null) {
    global $wpdb; $r = $r ?: eh_stm_round(); eh_stm_expire($r); $t = eh_stm_table(); $now = time();
    return [
        'round' => $r,
        'seen' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT uid) FROM $t WHERE round = %s", $r['id'])),
        'live' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE round = %s AND started > 0 AND submitted = 0 AND last > %d", $r['id'], $now - 120)),
        'started' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE round = %s AND started > 0", $r['id'])),
        'done' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $t WHERE round = %s AND submitted > 0 AND counted = 1", $r['id'])),
        'top' => (int) $wpdb->get_var($wpdb->prepare("SELECT MAX(score) FROM $t WHERE round = %s AND submitted > 0", $r['id'])),
    ];
}

/* Owner controls from the Telegram bot: close the mock now, or open it now. */
function eh_stm_close($id) {
    global $wpdb; $now = time();
    $ctl = get_option('eh_stm_ctl', []); if (!is_array($ctl)) $ctl = [];
    $ctl[$id] = array_merge($ctl[$id] ?? [], ['end' => $now]); update_option('eh_stm_ctl', $ctl, false);
    $wpdb->query($wpdb->prepare('UPDATE ' . eh_stm_table() . ' SET deadline = %d WHERE round = %s AND started > 0 AND submitted = 0 AND deadline > %d', $now, $id, $now));
}
function eh_stm_open($id) {
    $ctl = get_option('eh_stm_ctl', []); if (!is_array($ctl)) $ctl = [];
    $c = $ctl[$id] ?? []; unset($c['end']); $c['start'] = time(); $ctl[$id] = $c; update_option('eh_stm_ctl', $ctl, false);
}
function eh_stm_bot_view($confirm = false) {
    $m = eh_stm_counts(); $r = $m['round']; $st = eh_stm_status($r);
    $tz = new DateTimeZone('Asia/Kolkata'); $f = function ($t) use ($tz) { return (new DateTime('@' . $t))->setTimezone($tz)->format('d M, h:i A'); };
    $sl = ['soon' => '⏳ अभी खुला नहीं', 'login' => '🟡 लॉग-इन खुला, पेपर जल्द', 'live' => '🟢 LIVE — चल रहा है', 'over' => '🔴 बंद'][$st];
    $t = '📝 <b>' . esc_html($r['name']) . "</b>\n" . $sl . "\n" . $f($r['start_ts']) . ' → ' . $f($r['end_ts'])
        . "\n\n👤 पेज खोला (लॉग-इन)   " . $m['seen'] . "\n🟢 अभी पेपर दे रहे   " . $m['live'] . "\n▶️ पेपर शुरू किया   " . $m['started'] . "\n✅ सबमिट किया   " . $m['done'] . "\n🏆 टॉप स्कोर   " . $m['top'] . ' / 360';
    if ($confirm) {
        $t .= "\n\n⚠️ <b>मॉक अभी बंद करना है?</b>\nजो पेपर दे रहे हैं उनका पेपर अपने आप सबमिट हो जाएगा और कोई नया शुरू नहीं कर पाएगा।";
        return [$t, ['inline_keyboard' => [[['text' => '✅ हाँ, बंद करो', 'callback_data' => 'stm:close:go'], ['text' => '❌ नहीं', 'callback_data' => 'mock']]]]];
    }
    $kb = [];
    if ($st === 'live') $kb[] = [['text' => '⏹ मॉक अभी बंद करो', 'callback_data' => 'stm:close']];
    else $kb[] = [['text' => '▶️ मॉक अभी खोलो', 'callback_data' => 'stm:open']];
    $kb[] = [['text' => '🔄 Refresh', 'callback_data' => 'mock'], ['text' => '☰ Menu', 'callback_data' => 'menu']];
    return [$t, ['inline_keyboard' => $kb]];
}
