<?php
/* Super TET live mock: rounds, attempts, server-side timer, scoring, rank, percentile, admin. */
if (!defined('ABSPATH')) exit;

define('EH_STM_DB', '3');
define('EH_STM_SECS', 7200);

function eh_stm_table() { global $wpdb; return $wpdb->prefix . 'eh_mock'; }

/* Rounds: times are IST. 't1' = team trial, 'm1' = the real live mock on 22 Nov. */
function eh_stm_rounds() {
    $tz = new DateTimeZone('Asia/Kolkata');
    $ts = function ($s) use ($tz) { return (new DateTime($s, $tz))->getTimestamp(); };
    $o = get_option('eh_stm_rounds');
    $list = is_array($o) && $o ? $o : [
        ['id' => 't2', 'name' => 'SUPER TET - TRIAL MOCK', 'login' => '2026-11-21 21:00', 'start' => '2026-11-21 21:00', 'end' => '2026-11-22 08:40', 'paper' => 'supertet-mock1'],
        ['id' => 'm1', 'name' => 'SUPER TET - LIVE MOCK 1', 'login' => '2026-11-22 08:40', 'start' => '2026-11-22 09:00', 'end' => '2026-11-22 23:59', 'paper' => 'supertet-mock1'],
        ['id' => 'm2', 'name' => 'SUPER TET - LIVE MOCK 2', 'login' => '2026-11-25 09:10', 'start' => '2026-11-25 09:30', 'end' => '2026-11-25 23:59', 'paper' => 'supertet-mock2'],
        ['id' => 'p1', 'name' => 'SUPER TET - FULL LENGTH MOCK 1', 'login' => '2026-11-28 00:00', 'start' => '2026-11-28 00:00', 'end' => '2027-03-31 23:59', 'paper' => 'supertet-practice1', 'kind' => 'practice'],
        ['id' => 'p2', 'name' => 'SUPER TET - FULL LENGTH MOCK 2', 'login' => '2026-11-28 00:00', 'start' => '2026-11-28 00:00', 'end' => '2027-03-31 23:59', 'paper' => 'supertet-practice2', 'kind' => 'practice'],
        ['id' => 'p3', 'name' => 'SUPER TET - FULL LENGTH MOCK 3', 'login' => '2026-11-28 00:00', 'start' => '2026-11-28 00:00', 'end' => '2027-03-31 23:59', 'paper' => 'supertet-practice3', 'kind' => 'practice'],
    ];
    $list = array_map(function ($r) { $r['series'] = $r['series'] ?? 'stet'; return $r; }, $list);
    if (function_exists('eh_tp_rounds_all')) $list = array_merge($list, eh_tp_rounds_all());
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
    $all = array_values(array_filter($all, function ($r) { return $r['series'] === 'stet' && ($r['kind'] ?? '') !== 'practice'; }));
    foreach ($all as $r) if ($now < $r['end_ts']) return $r;
    return end($all);
}
/* A past (finished) live mock can still be opened read-only with ?r=<id> — results, solutions, rank. */
function eh_stm_view_round() {
    if (function_exists('eh_tp_req_round') && ($t = eh_tp_req_round())) return $t;
    $id = sanitize_key($_GET['r'] ?? '');
    if ($id && $id[0] === 'm') { $r = eh_stm_round($id); if ($r && $r['id'] === $id && time() >= $r['end_ts']) return $r; }
    return eh_stm_round();
}
function eh_stm_paper($r) {
    static $c = [];
    $k = preg_replace('#[^a-z0-9/-]#', '', $r['paper']);
    if (!isset($c[$k])) {
        $rd = function ($f) { return json_decode((string) @file_get_contents(EH_DIR . '/data/' . $f . '.json'), true) ?: []; };
        $c[$k] = !empty($r['gs']) ? array_merge($rd(preg_replace('#[^a-z0-9/-]#', '', $r['gs'])), $rd(preg_replace('#[^a-z0-9/-]#', '', $r['paper']))) : $rd($k);
    }
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
        paused int(10) unsigned NOT NULL DEFAULT 0,
        used int(10) unsigned NOT NULL DEFAULT 0,
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
/* The clock runs only while the exam screen is open. A paused attempt (page left, or no heartbeat for 2 min)
   keeps its remaining time; its effective deadline moves forward with the clock, capped at the round end. */
function eh_stm_pause_from($row, $now) {
    if (!$row['started'] || $row['submitted']) return 0;
    if ((int) ($row['paused'] ?? 0)) return (int) $row['paused'];
    if ($now - (int) $row['last'] > 120) return (int) $row['last'];
    return 0;
}
function eh_stm_eff($r, $row, $now = null) {
    $now = $now ?: time(); $d = (int) $row['deadline']; $ps = eh_stm_pause_from($row, $now);
    if ($ps) $d = min((int) $r['end_ts'], $d + max(0, $now - $ps));
    return $d;
}
function eh_stm_finish_row($r, $row, $when) {
    global $wpdb;
    $allot = min(eh_stm_secs($r), max(0, (int) $r['end_ts'] - (int) $row['started']));
    $used = max(0, min($allot, $allot - max(0, eh_stm_eff($r, $row, $when) - $when)));
    $ans = json_decode($row['ans'] ?: '{}', true) ?: [];
    [$c, $w, $s] = eh_stm_score($r, $ans);
    $adm = user_can((int) $row['uid'], 'manage_options');
    /* Mock 1: the latest attempt counted. From Mock 2 on: only the FIRST submitted attempt counts in the merit list; re-attempts are practice. */
    $cnt = $adm ? 0 : 1;
    if (!$adm && $row['round'] === 'm1') $wpdb->query($wpdb->prepare('UPDATE ' . eh_stm_table() . ' SET counted = 0 WHERE round = %s AND uid = %d AND att < %d', $row['round'], $row['uid'], $row['att']));
    elseif (!$adm && (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_stm_table() . ' WHERE round = %s AND uid = %d AND att < %d AND submitted > 0', $row['round'], $row['uid'], $row['att']))) $cnt = 0;
    $wpdb->update(eh_stm_table(), ['submitted' => $when, 'correct' => $c, 'wrong' => $w, 'score' => $s, 'counted' => $cnt, 'used' => $used, 'paused' => 0], ['id' => $row['id']]);
}
/* Attempts whose time ran out without a submit are submitted at their deadline. */
function eh_stm_expire($r) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . eh_stm_table() . ' WHERE round = %s AND started > 0 AND submitted = 0 AND deadline < %d', $r['id'], time() - 20), ARRAY_A);
    $n = time(); foreach ($rows as $row) { $d = eh_stm_eff($r, $row, $n); if ($d < $n - 20) eh_stm_finish_row($r, $row, $d); }
}
function eh_stm_public_q($r) {
    return array_map(function ($q) { $o = ['q' => $q['q'], 'o' => $q['o']]; if (!empty($q['p'])) $o['p'] = $q['p']; if (!empty($q['qe'])) { $o['qe'] = $q['qe']; $o['oe'] = $q['oe']; } return $o; }, eh_stm_paper($r));
}
function eh_stm_status($r) {
    $n = time();
    if (function_exists('eh_tp_is') && eh_tp_is($r) && !eh_tp_ready($r)) return 'soon';
    if ($n < $r['login_ts']) return 'soon';
    if ($n < $r['start_ts']) return 'login';
    if ($n < $r['end_ts']) return 'live';
    return 'over';
}
function eh_stm_roll($uid) { return 'XL26' . str_pad((string) $uid, 6, '0', STR_PAD_LEFT); }
function eh_stm_round_out($r) {
    return ['id' => $r['id'], 'name' => $r['name'], 'login' => $r['login_ts'], 'start' => $r['start_ts'], 'end' => $r['end_ts'], 'status' => eh_stm_status($r), 'series' => $r['series'] ?? 'stet', 'sub' => $r['sub'] ?? '', 'kind' => $r['kind'] ?? ''];
}
function eh_stm_attempt_out($row, $r) {
    $ps = eh_stm_pause_from($row, time());
    $o = ['started' => (int) $row['started'], 'deadline' => eh_stm_eff($r, $row), 'paused' => $ps ? 1 : 0, 'left' => max(0, eh_stm_eff($r, $row) - time()), 'submitted' => (int) $row['submitted'],
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
        'started' => (int) $row['started'], 'used' => (int) ($row['used'] ?? 0) ?: max(0, min(eh_stm_secs($r), (int) $row['submitted'] - (int) $row['started'])), 'tabs' => (int) $row['tabs'],
        'ans' => (object) (json_decode($row['ans'] ?: '{}', true) ?: []), 'tq' => (object) (json_decode($row['tq'] ?: '{}', true) ?: []),
        'key' => array_map(function ($q) { return $q['a']; }, $Q), 'exp' => array_map(function ($q) { return $q['e'] ?? ''; }, $Q),
        'q' => eh_stm_public_q($r), 'att' => (int) $row['att'], 'admin_test' => user_can((int) $row['uid'], 'manage_options'), 'practice' => !user_can((int) $row['uid'], 'manage_options') && !(int) $row['counted'] && $row['submitted'] > 0,
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
        $r = eh_stm_view_round(); $row = time() >= $r['end_ts'] ? (eh_stm_row($r['id'], get_current_user_id()) ?: eh_stm_touch($r)) : eh_stm_touch($r);
        global $wpdb; $now = time(); $up = ['last' => $now];
        if ($row['started'] && !$row['submitted']) {
            $d = eh_stm_eff($r, $row, $now);
            if ($d < $now - 20) { eh_stm_finish_row($r, $row, $d); $up = ['last' => $now]; }
            elseif (!(int) ($row['paused'] ?? 0)) $up['paused'] = $row['paused'] = eh_stm_pause_from($row, $now) ?: min($now, (int) $row['last']);
        }
        $wpdb->update(eh_stm_table(), $up, ['id' => $row['id']]); $row = eh_stm_row($r['id'], $row['uid']);
        return ['now' => time(), 'round' => eh_stm_round_out($r), 'roll' => eh_stm_roll((int) $row['uid']), 'name' => $row['name'], 'attempt' => eh_stm_attempt_out($row, $r)];
    }]);
    register_rest_route('eh/v1', '/stm/start', ['methods' => 'POST', 'permission_callback' => $auth, 'callback' => function (WP_REST_Request $q) {
        $tp = function_exists('eh_tp_req_round') ? eh_tp_req_round() : null;
        if (!$tp && !empty($_GET['r']) && sanitize_key($_GET['r']) !== eh_stm_round()['id']) return new WP_Error('past', 'यह मॉक समाप्त हो चुका है', ['status' => 403]);
        global $wpdb;
        $r = $tp ?: eh_stm_round(); $row = eh_stm_touch($r); $now = time();
        if (eh_stm_status($r) !== 'live') return new WP_Error('closed', 'परीक्षा अभी खुली नहीं है', ['status' => 403]);
        if ($row['submitted'] && !empty($q['retry'])) {
            $u = wp_get_current_user();
            $wpdb->insert(eh_stm_table(), ['round' => $r['id'], 'uid' => $u->ID, 'att' => (int) $row['att'] + 1, 'name' => $row['name'], 'email' => $row['email'], 'seen' => $now, 'last' => $now, 'ans' => '{}', 'st' => '{}', 'tq' => '{}']);
            $row = eh_stm_row($r['id'], $u->ID);
        }
        if (!$row['started']) {
            $wpdb->update(eh_stm_table(), ['started' => $now, 'deadline' => min($now + eh_stm_secs($r), $r['end_ts']), 'last' => $now, 'dev' => wp_is_mobile() ? 'mobile' : 'pc'], ['id' => $row['id']]);
            $row = eh_stm_row($r['id'], $row['uid']);
        }
        return ['now' => $now, 'attempt' => eh_stm_attempt_out($row, $r)];
    }]);
    register_rest_route('eh/v1', '/stm/resume', ['methods' => 'POST', 'permission_callback' => $auth, 'callback' => function () {
        $tp = function_exists('eh_tp_req_round') ? eh_tp_req_round() : null;
        if (!$tp && !empty($_GET['r']) && sanitize_key($_GET['r']) !== eh_stm_round()['id']) return new WP_Error('past', 'यह मॉक समाप्त हो चुका है', ['status' => 403]);
        global $wpdb;
        $r = $tp ?: eh_stm_round(); $row = eh_stm_row($r['id'], get_current_user_id()); $now = time();
        if (!$row || !$row['started']) return new WP_Error('no', 'no attempt', ['status' => 400]);
        if ($row['submitted']) return ['now' => $now, 'submitted' => true];
        $d = eh_stm_eff($r, $row, $now);
        if ($d < $now - 20) { eh_stm_finish_row($r, $row, $d); return ['now' => $now, 'submitted' => true]; }
        $wpdb->update(eh_stm_table(), ['deadline' => $d, 'paused' => 0, 'last' => $now], ['id' => $row['id']]);
        return ['now' => $now, 'deadline' => $d];
    }]);
    register_rest_route('eh/v1', '/stm/save', ['methods' => 'POST', 'permission_callback' => $auth, 'callback' => function (WP_REST_Request $q) {
        $tp = function_exists('eh_tp_req_round') ? eh_tp_req_round() : null;
        if (!$tp && !empty($_GET['r']) && sanitize_key($_GET['r']) !== eh_stm_round()['id']) return new WP_Error('past', 'यह मॉक समाप्त हो चुका है', ['status' => 403]);
        global $wpdb;
        $r = $tp ?: eh_stm_round(); $row = eh_stm_row($r['id'], get_current_user_id()); $now = time();
        if (!$row || !$row['started']) return new WP_Error('no', 'no attempt', ['status' => 400]);
        if ($row['submitted']) return ['ok' => true, 'submitted' => true];
        $d = eh_stm_eff($r, $row, $now);
        if ($d < $now - 20) { eh_stm_finish_row($r, $row, $d); return ['ok' => true, 'submitted' => true]; }
        if ($d !== (int) $row['deadline'] || (int) $row['paused']) { $wpdb->update(eh_stm_table(), ['deadline' => $d, 'paused' => 0], ['id' => $row['id']]); $row['deadline'] = $d; $row['paused'] = 0; }
        $ans = eh_stm_clean_map($q['ans'], '/^[ABCD]$/');
        $st = eh_stm_clean_map($q['st'], '/^(na|an|mr|am)$/');
        $tq = array_map('intval', eh_stm_clean_map($q['tq'], '/^\d{1,5}$/'));
        $wpdb->update(eh_stm_table(), ['ans' => wp_json_encode((object) $ans), 'st' => wp_json_encode((object) $st), 'tq' => wp_json_encode((object) $tq),
            'answered' => count($ans), 'cur' => max(0, min(399, (int) $q['cur'])), 'tabs' => max((int) $row['tabs'], min(999, (int) $q['tabs'])), 'last' => $now], ['id' => $row['id']]);
        if (!empty($q['submit'])) { $row = eh_stm_row($r['id'], $row['uid']); eh_stm_finish_row($r, $row, min($now, (int) $row['deadline'])); return ['ok' => true, 'submitted' => true]; }
        return ['ok' => true, 'now' => $now, 'deadline' => (int) $row['deadline']];
    }]);
    register_rest_route('eh/v1', '/stm/result', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_view_round(); $row = eh_stm_row($r['id'], get_current_user_id());
        if (!$row || !$row['started']) return new WP_Error('no', 'no attempt', ['status' => 404]);
        if (!$row['submitted']) { $d = eh_stm_eff($r, $row); if (time() <= $d + 20) return new WP_Error('run', 'running', ['status' => 409]); eh_stm_finish_row($r, $row, $d); }
        return eh_stm_result($r, $row);
    }]);
    register_rest_route('eh/v1', '/stm/live', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_view_round(); $row = eh_stm_row($r['id'], get_current_user_id());
        if (!$row || !$row['submitted']) return new WP_Error('no', 'no result', ['status' => 404]);
        return eh_stm_live($r, (int) $row['score']);
    }]);
    register_rest_route('eh/v1', '/stm/paper', ['methods' => 'GET', 'permission_callback' => $auth, 'callback' => function () {
        $r = eh_stm_view_round(); $row = eh_stm_row($r['id'], get_current_user_id());
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
    $wpdb->query($wpdb->prepare('UPDATE ' . eh_stm_table() . ' SET deadline = LEAST(deadline, %d), paused = 0 WHERE round = %s AND started > 0 AND submitted = 0', $now, $id));
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
    if (function_exists('eh_stm_mail_counts')) { $mc = eh_stm_mail_counts(); $mo = eh_stm_mail_opt(); $t .= "\n\n📧 Mail " . ($mo['on'] ? 'चालू' : 'बंद') . " · भेजे " . $mc['sent'] . " · बाकी " . $mc['pending'] . " · फेल " . $mc['fail'] . (function () use ($mo) { $c = eh_stm_mail_24h(); $p = function ($a) use ($mo) { return (int) ($mo['pause' . $a] ?? 0) > time() ? ' ⏸' : ''; };
        return "\n   24 घंटे: Gmail 1 → " . $c[1] . '/' . EH_STM_MAIL_DAILY . $p(1) . ($mo['from2'] ? " · Gmail 2 → " . $c[2] . '/' . EH_STM_MAIL_DAILY . $p(2) : ''); })(); }
    if (function_exists('eh_tp_rounds')) { global $wpdb; $tt = eh_stm_table(); $now2 = time();
        foreach (['tgt' => 'tg', 'pgt' => 'pg'] as $sr => $px) {
            $lv = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tt WHERE round LIKE %s AND started > 0 AND submitted = 0 AND last > %d", $px . '%', $now2 - 120));
            $dn = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tt WHERE round LIKE %s AND submitted > 0 AND counted = 1", $px . '%'));
            $rdy = count(array_filter(eh_tp_rounds(), function ($x) use ($sr) { return $x['series'] === $sr && eh_tp_ready($x); }));
            $t .= "\n\n📚 <b>UP " . strtoupper($sr) . "</b> (" . $rdy . " विषय लाइव)\n🟢 अभी दे रहे " . $lv . " · ✅ सबमिट " . $dn; } }
    if ($confirm) {
        $t .= "\n\n⚠️ <b>मॉक अभी बंद करना है?</b>\nजो पेपर दे रहे हैं उनका पेपर अपने आप सबमिट हो जाएगा और कोई नया शुरू नहीं कर पाएगा।";
        return [$t, ['inline_keyboard' => [[['text' => '✅ हाँ, बंद करो', 'callback_data' => 'stm:close:go'], ['text' => '❌ नहीं', 'callback_data' => 'mock']]]]];
    }
    $kb = [];
    if ($st === 'live') $kb[] = [['text' => '⏹ मॉक अभी बंद करो', 'callback_data' => 'stm:close']];
    else $kb[] = [['text' => '▶️ मॉक अभी खोलो', 'callback_data' => 'stm:open']];
    if (function_exists('eh_tgapp_url')) $kb[] = [['text' => '🛠️ Admin Panel (Mini App)', 'web_app' => ['url' => eh_tgapp_url()]]];
    $kb[] = [['text' => '🔄 Refresh', 'callback_data' => 'mock'], ['text' => '☰ Menu', 'callback_data' => 'menu']];
    return [$t, ['inline_keyboard' => $kb]];
}

/* Admin report: summary, section averages, full merit list. */
function eh_stm_secmap($rd = null) {
    $r = function ($a, $b) { return range($a - 1, $b - 1); };
    $rd = $rd ?: eh_stm_round();
    if (function_exists('eh_tp_is') && eh_tp_is($rd)) {
        if (($rd['kind'] ?? '') === 'sec') return [[$rd['sub'], $r(1, 90)]];
        if (($rd['kind'] ?? '') === 'topic') return [[$rd['topic'], $r(1, 50)]];
        if (($rd['kind'] ?? '') === 'gs') return [['सामान्य अध्ययन', $r(1, 30)]];
        return [['सामान्य अध्ययन', $r(1, 30)], [$rd['sub'], $r(31, 120)]];
    }
    if (($rd['paper'] ?? '') !== 'supertet-mock1') return [['सामान्य ज्ञान एवं समसामयिकी', $r(1, 25)], ['तार्किक ज्ञान', $r(26, 30)], ['हिंदी, संस्कृत एवं अंग्रेजी', $r(31, 60)], ['विज्ञान', $r(61, 68)], ['गणित', $r(69, 84)], ['पर्यावरण एवं सामाजिक अध्ययन', $r(85, 92)], ['शिक्षण कौशल', $r(93, 100)], ['बाल मनोविज्ञान', $r(101, 108)], ['सूचना तकनीक (ICT)', $r(109, 112)], ['जीवन कौशल, प्रबंधन एवं अभिवृत्ति', $r(113, 120)]];
    return [['सामान्य ज्ञान एवं समसामयिकी', $r(1, 25)], ['तार्किक ज्ञान', $r(26, 30)], ['हिंदी, संस्कृत एवं अंग्रेजी', $r(31, 60)], ['विज्ञान', $r(61, 68)], ['गणित', $r(69, 84)], ['पर्यावरण एवं सामाजिक अध्ययन', $r(85, 92)], ['शिक्षण कौशल', $r(93, 100)], ['बाल मनोविज्ञान', $r(101, 108)], ['सूचना तकनीक (ICT)', array_merge($r(109, 112), $r(117, 120))], ['जीवन कौशल / प्रबंधन', $r(113, 116)]];
}
add_action('rest_api_init', function () {
    register_rest_route('eh/v1', '/stm/report', ['methods' => 'GET', 'permission_callback' => function () { return current_user_can('manage_options'); }, 'callback' => function (WP_REST_Request $q) { return eh_stm_report_data(sanitize_key($q['round'] ?? '')); }]);
});
/* Full merit/report data for a round — used by the website Admin Panel and the Telegram Mini App. */
function eh_stm_report_data($rid = '') {
        global $wpdb; $t = eh_stm_table();
        $r = eh_stm_round($rid); eh_stm_expire($r);
        $Q = eh_stm_paper($r); $S = eh_stm_secmap($r);
        $all = $wpdb->get_results($wpdb->prepare("SELECT uid, att, counted, name, email, started, submitted, last, correct, wrong, score, tabs, dev, ans FROM $t WHERE round = %s", $r['id']), ARRAY_A);
        $att = []; foreach ($all as $x) if ($x['started']) $att[$x['uid']] = ($att[$x['uid']] ?? 0) + 1;
        $m = array_values(array_filter($all, function ($x) { return $x['submitted'] > 0 && $x['counted']; }));
        usort($m, function ($a, $b) { return $b['score'] <=> $a['score'] ?: (($a['submitted'] - $a['started']) <=> ($b['submitted'] - $b['started'])); });
        $sc = array_map(function ($x) { return (int) $x['score']; }, $m); $n = count($sc);
        $sorted = $sc; sort($sorted);
        $secTot = array_fill(0, count($S), 0);
        $rows = [];
        foreach ($m as $x) {
            $s = (int) $x['score']; $a = json_decode($x['ans'] ?: '{}', true) ?: [];
            $ps = []; foreach ($S as $k => $sec) { $c = 0; $w = 0; foreach ($sec[1] as $i) { if (!isset($a[$i])) continue; if ($a[$i] === $Q[$i]['a']) $c++; else $w++; } $secTot[$k] += $c * 3 - $w; $ps[] = [$c, $w]; }
            $rows[] = ['rank' => count(array_filter($sc, function ($v) use ($s) { return $v > $s; })) + 1,
                'pct' => $n ? round(count(array_filter($sc, function ($v) use ($s) { return $v <= $s; })) * 100 / $n, 2) : 0,
                'uid' => (int) $x['uid'], 'name' => $x['name'], 'email' => $x['email'], 'roll' => eh_stm_roll((int) $x['uid']), 'score' => $s, 'c' => (int) $x['correct'], 'w' => (int) $x['wrong'],
                'min' => round(($x['submitted'] - $x['started']) / 60), 'att' => (int) ($att[$x['uid']] ?? 1), 'tabs' => (int) $x['tabs'], 'dev' => $x['dev'], 'secs' => $ps];
        }
        $users = count(array_unique(array_column($all, 'uid')));
        return ['round' => eh_stm_round_out($r), 'rounds' => array_map('eh_stm_round_out', eh_stm_rounds()), 'now' => time(),
            'stats' => ['users' => $users, 'started' => count($att), 'attempts' => array_sum($att), 'merit' => $n,
                'live' => count(array_filter($all, function ($x) { return $x['started'] && !$x['submitted'] && time() - $x['last'] < 120; })),
                'avg' => $n ? round(array_sum($sc) / $n, 2) : 0, 'max' => $n ? max($sc) : 0, 'min' => $n ? min($sc) : 0,
                'median' => $n ? ($n % 2 ? $sorted[intdiv($n, 2)] : ($sorted[$n / 2 - 1] + $sorted[$n / 2]) / 2) : 0,
                'avgc' => $n ? round(array_sum(array_column($m, 'correct')) / $n, 1) : 0, 'avgw' => $n ? round(array_sum(array_column($m, 'wrong')) / $n, 1) : 0],
            'sections' => array_map(function ($sec, $k) use ($secTot, $n) { return ['n' => $sec[0], 'q' => count($sec[1]), 'avg' => $n ? round($secTot[$k] / $n, 2) : 0]; }, $S, array_keys($S)),
            'rows' => $rows];
}

