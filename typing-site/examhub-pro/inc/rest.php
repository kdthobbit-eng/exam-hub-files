<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
    register_rest_route('eh/v1', '/result', [
        'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'eh_rest_save',
    ]);
    register_rest_route('eh/v1', '/leaderboard', [
        'methods' => 'GET', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            $rows = eh_leaderboard(sanitize_title($r['exam'] ?? ''), preg_replace('/[^0-9-]/', '', $r['date'] ?? ''), 50);
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
    $live = !empty($p['live']) && $p['live'] === wp_date('Y-m-d') ? $p['live'] : null;
    $words = max(0, min(5000, (int) ($p['words'] ?? 0)));
    $mins = max(0.01, min(60, (float) ($p['minutes'] ?? 0)));
    $net = max(0, min(250, (int) ($p['net'] ?? 0)));
    if ($words / $mins > 260) return ['ok' => false];
    $wpdb->insert(eh_table(), [
        'user_id' => $uid, 'name' => $name, 'exam' => $exam,
        'layout' => sanitize_key($p['layout'] ?? ''), 'passage' => sanitize_key($p['passage'] ?? ''),
        'minutes' => $mins, 'keys_count' => max(0, min(30000, (int) ($p['keys'] ?? 0))), 'words' => $words,
        'full_m' => max(0, (int) ($p['full'] ?? 0)), 'half_m' => max(0, (int) ($p['half'] ?? 0)),
        'backspaces' => max(0, (int) ($p['backspaces'] ?? 0)),
        'gross' => max(0, min(300, (int) ($p['gross'] ?? 0))), 'net' => $net,
        'acc' => max(0, min(100, (float) ($p['acc'] ?? 0))), 'err' => max(0, min(100, (float) ($p['err'] ?? 0))),
        'pass' => !empty($p['pass']) ? 1 : 0, 'live_date' => $live, 'ip_hash' => $ip, 'created' => current_time('mysql'),
        'kerr' => max(0, min(30000, (int) ($p['kerr'] ?? 0))),
        'title' => mb_substr(sanitize_text_field($p['title'] ?? ''), 0, 180),
        'rkey' => $rkey = strtolower(wp_generate_password(8, false, false)),
        'typed' => mb_substr(sanitize_textarea_field($p['typed'] ?? ''), 0, 20000),
        'src' => mb_substr(sanitize_textarea_field($p['src'] ?? ''), 0, 25000),
        'mist' => wp_json_encode(array_slice(array_values(array_filter(array_map(function ($x) {
            return is_array($x) && count($x) === 2 ? [mb_substr(sanitize_text_field($x[0]), 0, 40), mb_substr(sanitize_text_field($x[1]), 0, 40)] : null;
        }, (array) ($p['mist'] ?? [])))), 0, 60), JSON_UNESCAPED_UNICODE),
    ]);
    $rid = (int) $wpdb->insert_id;
    delete_transient('eh_attempts');
    $rank = 0;
    if ($live) {
        $rank = 1 + (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_table() . ' WHERE live_date = %s AND net > %d', $live, $net));
    }
    return ['ok' => true, 'rank' => $rank, 'id' => $rid, 'url' => $rid ? eh_result_url($rid, $rkey) : ''];
}
