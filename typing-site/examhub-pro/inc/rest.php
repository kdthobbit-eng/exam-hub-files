<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
    register_rest_route('eh/v1', '/result', [
        'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'eh_rest_save',
    ]);
    register_rest_route('eh/v1', '/leaderboard', [
        'methods' => 'GET', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            $rows = eh_leaderboard(sanitize_title($r['exam'] ?? ''), preg_replace('/[^0-9-]/', '', $r['date'] ?? ''), 50, 0, sanitize_key($r['passage'] ?? ''));
            return array_map(function ($x) { return ['name' => eh_display_name($x), 'net' => (int) $x['net'], 'acc' => (float) $x['acc'], 'pass' => (int) $x['pass'], 'exam' => $x['exam']]; }, $rows);
        },
    ]);
});

function eh_rest_save(WP_REST_Request $r) {
    global $wpdb;
    $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . wp_salt()), 0, 16);
    $k = 'eh_rl_' . $ip;
    $n = (int) get_transient($k);
    if ($n > 30) return new WP_REST_Response(['ok' => false, 'msg' => 'Too many'], 429);
    set_transient($k, $n + 1, HOUR_IN_SECONDS);

    $p = $r->get_json_params();
    $exam = sanitize_title($p['exam'] ?? '');
    if ($exam !== 'demo' && !eh_exam($exam)) return ['ok' => false];
    $uid = get_current_user_id();
    $name = $uid ? '' : mb_substr(sanitize_text_field($p['name'] ?? ''), 0, 40);
    if (!empty($p['live']) && !$uid) return new WP_REST_Response(['ok' => false, 'msg' => 'Please log in to take Live Typing.'], 401);
    $live = !empty($p['live']) && $p['live'] === wp_date('Y-m-d') ? $p['live'] : null;
    $words = max(0, min(5000, (int) ($p['words'] ?? 0)));
    $mins = max(0.01, min(60, (float) ($p['minutes'] ?? 0)));
    $net = max(0, min(250, (int) ($p['net'] ?? 0)));
    if ($words / $mins > 260) return ['ok' => false];
    /* never trust the browser's numbers alone: key count is capped by the typed text (each word worth at most 20 letters),
       and for the UPSSSC rule speed and result are worked out again here with the same formula as the result page */
    $typed = mb_substr(sanitize_textarea_field($p['typed'] ?? ''), 0, 20000);
    $keys = max(0, min(30000, (int) ($p['keys'] ?? 0)));
    if (trim($typed) !== '') $keys = eh_kcap_server($keys, $typed);
    $full = max(0, (int) ($p['full'] ?? 0)); $half = max(0, (int) ($p['half'] ?? 0));
    $gross = max(0, min(300, (int) ($p['gross'] ?? 0))); $pass = !empty($p['pass']) ? 1 : 0;
    $ex = $exam !== 'demo' ? eh_exam($exam) : null;
    /* test length the person chose (practice can be shorter or longer than the exam); live tests always use the exam's own length */
    $dur = $ex ? (float) $ex['minutes'] : 5;
    if (!$live && !empty($p['dur'])) $dur = max(1, min(60, (float) $p['dur']));
    if ($ex && ($ex['eval'] ?? '') === 'upsssc' && !empty($ex['wpm'])) {
        $m = max($mins, $dur, 0.01); $kw = $keys > 0 ? $keys / 5 : $words;
        $sp = max(0, $kw - max(0, $full + $half / 2 - 5) * 6) / $m;
        $net = min($net, (int) floor($sp)); $gross = (int) round($kw / $m); $pass = $sp >= (float) $ex['wpm'] ? 1 : 0;
    }
    $net = min($net, max($gross, 0));
    $wpdb->insert(eh_table(), [
        'user_id' => $uid, 'name' => $name, 'exam' => $exam,
        'layout' => sanitize_key($p['layout'] ?? ''), 'passage' => sanitize_key($p['passage'] ?? ''),
        'minutes' => $mins, 'dur' => $dur, 'keys_count' => $keys, 'words' => $words,
        'full_m' => $full, 'half_m' => $half,
        'backspaces' => max(0, (int) ($p['backspaces'] ?? 0)),
        'gross' => $gross, 'net' => $net,
        'acc' => max(0, min(100, (float) ($p['acc'] ?? 0))), 'err' => max(0, min(100, (float) ($p['err'] ?? 0))),
        'pass' => $pass, 'live_date' => $live, 'ip_hash' => $ip, 'created' => current_time('mysql'),
        'kerr' => max(0, min(30000, (int) ($p['kerr'] ?? 0))),
        'title' => mb_substr(sanitize_text_field($p['title'] ?? ''), 0, 180),
        'rkey' => $rkey = strtolower(wp_generate_password(8, false, false)),
        'typed' => $typed,
        'src' => mb_substr(sanitize_textarea_field($p['src'] ?? ''), 0, 25000),
        'mist' => wp_json_encode(array_slice(array_values(array_filter(array_map(function ($x) {
            return is_array($x) && count($x) === 2 ? [mb_substr(sanitize_text_field($x[0]), 0, 40), mb_substr(sanitize_text_field($x[1]), 0, 40)] : null;
        }, (array) ($p['mist'] ?? [])))), 0, 60), JSON_UNESCAPED_UNICODE),
    ]);
    $rid = (int) $wpdb->insert_id;
    delete_transient('eh_attempts');
    $rank = 0;
    if ($live) {
        $rank = 1 + (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_table() . ' WHERE live_date = %s AND exam = %s AND passage = %s AND net > %d', $live, $exam, sanitize_key($p['passage'] ?? ''), $net));
    }
    return ['ok' => true, 'rank' => $rank, 'id' => $rid, 'url' => $rid ? eh_result_url($rid, $rkey) : ''];
}
