<?php
if (!defined('ABSPATH')) exit;

define('EH_DB_VER', '2');

function eh_table() { global $wpdb; return $wpdb->prefix . 'eh_results'; }

add_action('init', function () {
    if (get_option('eh_db_ver') === EH_DB_VER) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $t = eh_table();
    $c = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $t (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL DEFAULT 0,
        name varchar(80) NOT NULL DEFAULT '',
        exam varchar(160) NOT NULL DEFAULT '',
        layout varchar(20) NOT NULL DEFAULT '',
        passage varchar(40) NOT NULL DEFAULT '',
        minutes decimal(6,2) NOT NULL DEFAULT 0,
        keys_count int(11) NOT NULL DEFAULT 0,
        words int(11) NOT NULL DEFAULT 0,
        full_m int(11) NOT NULL DEFAULT 0,
        half_m int(11) NOT NULL DEFAULT 0,
        backspaces int(11) NOT NULL DEFAULT 0,
        gross int(11) NOT NULL DEFAULT 0,
        net int(11) NOT NULL DEFAULT 0,
        acc decimal(5,2) NOT NULL DEFAULT 0,
        err decimal(6,2) NOT NULL DEFAULT 0,
        pass tinyint(1) NOT NULL DEFAULT 0,
        live_date date NULL,
        ip_hash char(16) NOT NULL DEFAULT '',
        kerr int(11) NOT NULL DEFAULT 0,
        title varchar(190) NOT NULL DEFAULT '',
        rkey char(8) NOT NULL DEFAULT '',
        typed mediumtext NULL,
        src mediumtext NULL,
        mist text NULL,
        created datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY exam (exam),
        KEY user_id (user_id),
        KEY live_date (live_date),
        KEY created (created)
    ) $c;");
    update_option('eh_db_ver', EH_DB_VER);
});

function eh_attempt_counts() {
    $c = get_transient('eh_attempts');
    if (is_array($c)) return $c;
    global $wpdb;
    $rows = $wpdb->get_results('SELECT exam, COUNT(*) n FROM ' . eh_table() . ' GROUP BY exam', ARRAY_A);
    $c = [];
    foreach ((array) $rows as $r) $c[$r['exam']] = (int) $r['n'];
    set_transient('eh_attempts', $c, 10 * MINUTE_IN_SECONDS);
    return $c;
}

function eh_leaderboard($exam = '', $live_date = '', $limit = 20, $since_days = 0, $passage = '') {
    global $wpdb;
    $t = eh_table();
    $w = ['words > 0'];
    $a = [];
    if ($exam) { $w[] = 'exam = %s'; $a[] = $exam; }
    if ($live_date) { $w[] = 'live_date = %s'; $a[] = $live_date; }
    if ($passage) { $w[] = 'passage = %s'; $a[] = $passage; }
    if ($since_days) { $w[] = 'created >= %s'; $a[] = gmdate('Y-m-d H:i:s', time() - $since_days * DAY_IN_SECONDS); }
    $sql = "SELECT name, user_id, exam, net, gross, acc, err, keys_count, words, pass, created FROM $t WHERE " . implode(' AND ', $w) . " ORDER BY net DESC, acc DESC LIMIT " . (int) $limit;
    return $a ? $wpdb->get_results($wpdb->prepare($sql, $a), ARRAY_A) : $wpdb->get_results($sql, ARRAY_A);
}

function eh_recent($limit = 12) {
    global $wpdb;
    return $wpdb->get_results('SELECT name, user_id, exam, gross, net, acc, pass, created FROM ' . eh_table() . ' WHERE words >= 10 AND net > 0 ORDER BY id DESC LIMIT ' . (int) $limit, ARRAY_A);
}

function eh_display_name($row) {
    if (!empty($row['user_id'])) { $u = get_userdata($row['user_id']); if ($u) return $u->display_name; }
    return $row['name'] !== '' ? $row['name'] : 'Guest';
}

function eh_result_url($id, $key) { return home_url('/result/' . (int) $id . '-' . $key . '/'); }

function eh_get_result($id, $key) {
    global $wpdb;
    $r = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . eh_table() . ' WHERE id = %d', $id), ARRAY_A);
    if (!$r) return null;
    $own = $r['user_id'] && (int) $r['user_id'] === get_current_user_id();
    if (!$own && !hash_equals((string) $r['rkey'], (string) $key)) return null;
    return $r;
}

/* Saved results follow the current exam rules: when a rule or target changes, pass/fail is worked out again once.
   For UPSSSC-rule exams the net and gross speed are also re-worked with the commission's formula. Nothing is deleted. */
add_action('init', function () {
    if (!is_admin() || !current_user_can('manage_options')) return;
    $exams = eh_exams(); $sig = [];
    foreach ($exams as $e) $sig[] = $e['slug'] . '|' . $e['unit'] . '|' . $e['target'] . '|' . $e['minutes'] . '|' . ($e['allowed'] ?? '') . '|' . ($e['wpm'] ?? '') . '|' . ($e['eval'] ?? '');
    $ver = md5('v2' . implode(';', $sig));
    if (get_option('eh_rules_ver') === $ver) return;
    global $wpdb; $t = eh_table(); $n = 0;
    foreach ($exams as $e) {
        if (empty($e['target'])) continue;
        if (($e['eval'] ?? '') === 'upsssc' && !empty($e['wpm'])) {
            $sp = '(GREATEST(0, words - GREATEST(0, full_m + half_m / 2 - 5) * 5) / GREATEST(minutes, %f, 0.01))';
            $n += (int) $wpdb->query($wpdb->prepare("UPDATE $t SET pass = IF($sp >= %f, 1, 0), net = FLOOR($sp), gross = ROUND(words / GREATEST(minutes, %f, 0.01)) WHERE exam = %s AND words > 0",
                (float) $e['minutes'], (float) $e['wpm'], (float) $e['minutes'], (float) $e['minutes'], $e['slug']));
        } else {
            $col = $e['unit'] === 'keys' ? 'keys_count' : 'words';
            $n += (int) $wpdb->query($wpdb->prepare("UPDATE $t SET pass = IF($col >= %d AND err <= %f, 1, 0) WHERE exam = %s", (int) $e['target'], (float) ($e['allowed'] ?? 5), $e['slug']));
        }
    }
    update_option('eh_rules_ver', $ver, false);
    update_option('eh_rules_fixed', ['at' => current_time('mysql'), 'rows' => $n], false);
    delete_transient('eh_attempts');
}, 30);
