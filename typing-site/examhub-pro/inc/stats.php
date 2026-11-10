<?php
/* Visitor counting + admin report (visitors, typing tests, test takers' emails, live typing). */
if (!defined('ABSPATH')) exit;

define('EH_VIS_VER', '1');
function eh_vis_table() { global $wpdb; return $wpdb->prefix . 'eh_visits'; }

add_action('init', function () {
    if (get_option('eh_vis_ver') === EH_VIS_VER) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $t = eh_vis_table();
    $c = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $t (
        day date NOT NULL,
        vid char(16) NOT NULL DEFAULT '',
        views int(11) NOT NULL DEFAULT 1,
        PRIMARY KEY  (day,vid)
    ) $c;");
    update_option('eh_vis_ver', EH_VIS_VER);
    if (!get_option('eh_vis_since')) update_option('eh_vis_since', current_time('Y-m-d'), false);
});

/* one row per visitor per day; the visitor id is a salted hash, the IP itself is never stored */
add_action('template_redirect', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || is_admin() || is_feed() || is_preview() || is_robots() || is_404()) return;
    if (get_option('eh_vis_ver') !== EH_VIS_VER) return;
    if (is_user_logged_in() && current_user_can('edit_posts')) return;
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse|facebookexternalhit|whatsapp|telegram/i', $ua)) return;
    global $wpdb;
    $vid = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $ua . '|' . wp_salt('nonce')), 0, 16);
    $wpdb->query($wpdb->prepare('INSERT INTO ' . eh_vis_table() . ' (day, vid, views) VALUES (%s, %s, 1) ON DUPLICATE KEY UPDATE views = views + 1', current_time('Y-m-d'), $vid));
}, 20);

add_action('admin_menu', function () {
    add_submenu_page('eh-settings', 'Visitors & Tests', 'Visitors & Tests', 'manage_options', 'eh-stats', 'eh_stats_page');
}, 20);

function eh_stats_people_sql() { return "IF(user_id > 0, CONCAT('u', user_id), CONCAT('g', ip_hash))"; }

function eh_stats_takers($limit = 0) {
    global $wpdb;
    $rows = $wpdb->get_results('SELECT user_id, COUNT(*) n, SUM(live_date IS NOT NULL) lv, MAX(created) last FROM ' . eh_table() . ' WHERE user_id > 0 GROUP BY user_id ORDER BY last DESC' . ($limit ? ' LIMIT ' . (int) $limit : ''), ARRAY_A);
    if (!$rows) return [];
    $users = [];
    foreach (get_users(['include' => array_map('intval', array_column($rows, 'user_id')), 'fields' => ['ID', 'display_name', 'user_email']]) as $u) $users[(int) $u->ID] = $u;
    $out = [];
    foreach ($rows as $r) {
        $u = $users[(int) $r['user_id']] ?? null;
        if (!$u) continue;
        $out[] = ['name' => $u->display_name, 'email' => $u->user_email, 'tests' => (int) $r['n'], 'live' => (int) $r['lv'], 'last' => $r['last']];
    }
    return $out;
}

function eh_stats_page() {
    if (!current_user_can('manage_options')) return;
    global $wpdb;
    $v = eh_vis_table(); $t = eh_table(); $today = current_time('Y-m-d');
    $d7 = gmdate('Y-m-d', strtotime($today) - 6 * DAY_IN_SECONDS); $d30 = gmdate('Y-m-d', strtotime($today) - 29 * DAY_IN_SECONDS);
    $vis = function ($from) use ($wpdb, $v) {
        $r = $from ? $wpdb->get_row($wpdb->prepare("SELECT COUNT(DISTINCT vid) u, COALESCE(SUM(views),0) p FROM $v WHERE day >= %s", $from), ARRAY_A)
                   : $wpdb->get_row("SELECT COUNT(DISTINCT vid) u, COALESCE(SUM(views),0) p FROM $v", ARRAY_A);
        return [(int) ($r['u'] ?? 0), (int) ($r['p'] ?? 0)];
    };
    $days = $wpdb->get_results($wpdb->prepare("SELECT day, COUNT(*) u, SUM(views) p FROM $v WHERE day >= %s GROUP BY day ORDER BY day DESC", gmdate('Y-m-d', strtotime($today) - 13 * DAY_IN_SECONDS)), ARRAY_A);

    $P = eh_stats_people_sql();
    $tests = function ($where = '1=1') use ($wpdb, $t) { return (int) $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE $where"); };
    $people = function ($where = '1=1') use ($wpdb, $t, $P) { return (int) $wpdb->get_var("SELECT COUNT(DISTINCT $P) FROM $t WHERE $where"); };
    $t_all = $tests(); $t_today = $tests($wpdb->prepare('created >= %s', $today . ' 00:00:00')); $t_7 = $tests($wpdb->prepare('created >= %s', $d7 . ' 00:00:00'));
    $p_all = $people(); $p_live = $people('live_date IS NOT NULL'); $p_live_today = $people($wpdb->prepare('live_date = %s', $today));
    $p_reg = $people('user_id > 0'); $p_guest = $p_all - $p_reg;
    $reg_total = (int) (count_users()['total_users'] ?? 0);
    $lang = ['en' => 0, 'hi' => 0, 'other' => 0];
    foreach ((array) $wpdb->get_results("SELECT exam, COUNT(*) n FROM $t GROUP BY exam", ARRAY_A) as $r) {
        $e = eh_exam($r['exam']); $l = $e ? $e['lang'] : 'en';
        $lang[isset($lang[$l]) ? $l : 'other'] += (int) $r['n'];
    }
    $takers = eh_stats_takers(500);
    $n = 'number_format_i18n';
    $card = function ($big, $en, $hi, $small = '') { echo '<div class="ehs-c"><b>' . esc_html($big) . '</b><span>' . esc_html($en) . '<br><i>' . esc_html($hi) . '</i></span>' . ($small ? '<small>' . esc_html($small) . '</small>' : '') . '</div>'; };
    list($v1u, $v1p) = $vis($today); list($v7u, $v7p) = $vis($d7); list($v30u, $v30p) = $vis($d30); list($vau, $vap) = $vis('');
    ?>
    <div class="wrap ehs">
    <style>
      .ehs h2{margin:28px 0 10px;font-size:18px}.ehs h2 i{font-weight:400;color:#646970;font-style:normal;margin-left:8px}
      .ehs-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;max-width:1100px}
      .ehs-c{background:#fff;border:1px solid #dcdcde;border-left:4px solid #f26b1d;border-radius:6px;padding:14px 16px;display:flex;flex-direction:column;gap:4px}
      .ehs-c b{font-size:28px;line-height:1.1;color:#06213d}.ehs-c span{font-size:13px;color:#1d2327}.ehs-c i{color:#646970;font-style:normal}.ehs-c small{color:#646970}
      .ehs table{max-width:1100px}.ehs .ehs-note{color:#646970;max-width:900px}
    </style>
    <h1>Visitors &amp; Tests <span style="font-weight:400;color:#646970">/ विज़िटर और टेस्ट</span></h1>

    <h2>Visitors <i>वेबसाइट पर कितने लोग आए</i></h2>
    <div class="ehs-g"><?php
      $card($n($v1u), 'Visitors today', 'आज के विज़िटर', $n($v1p) . ' page views');
      $card($n($v7u), 'Last 7 days', 'पिछले 7 दिन', $n($v7p) . ' page views');
      $card($n($v30u), 'Last 30 days', 'पिछले 30 दिन', $n($v30p) . ' page views');
      $card($n($vau), 'Total visitors', 'कुल विज़िटर', $n($vap) . ' page views');
    ?></div>
    <p class="ehs-note">Counting started on <?php echo esc_html((string) get_option('eh_vis_since', $today)); ?>. One person on one device counts once. Search engine bots and admins are not counted. / गिनती इसी तारीख से शुरू हुई है। एक व्यक्ति एक डिवाइस पर एक बार गिना जाता है।</p>
    <?php if ($days): ?>
    <table class="widefat striped"><thead><tr><th>Date / तारीख</th><th>Visitors / विज़िटर</th><th>Page views</th></tr></thead><tbody>
      <?php foreach ($days as $d) echo '<tr><td>' . esc_html(wp_date('d M Y', strtotime($d['day']))) . '</td><td>' . esc_html($n($d['u'])) . '</td><td>' . esc_html($n($d['p'])) . '</td></tr>'; ?>
    </tbody></table>
    <?php endif; ?>

    <h2>Typing tests <i>टाइपिंग टेस्ट</i></h2>
    <div class="ehs-g"><?php
      $card($n($t_all), 'Tests taken (total)', 'कुल टेस्ट दिए गए', $n($t_today) . ' today · ' . $n($t_7) . ' in 7 days');
      $card($n($p_all), 'People who took a test', 'टेस्ट देने वाले लोग', $n($p_reg) . ' logged in · ' . $n($p_guest) . ' guests');
      $card($n($lang['en']), 'English tests', 'English टेस्ट');
      $card($n($lang['hi']), 'Hindi tests', 'हिंदी टेस्ट', $lang['other'] ? $n($lang['other']) . ' other languages' : '');
      $card($n($reg_total), 'Registered users', 'रजिस्टर्ड यूज़र', $n(max(0, $reg_total - $p_reg)) . ' have not taken a test');
      $q_pass = $tests('pass = 1 AND words > 0'); $fx = (array) get_option('eh_rules_fixed', []);
      $card($n($q_pass), 'Qualified results', 'पास हुए रिजल्ट', 'out of ' . $n($t_all) . ($fx ? ' · rules re-applied ' . ($fx['at'] ?? '') : ''));
    ?></div>

    <h2>Live Typing <i>लाइव टाइपिंग</i></h2>
    <div class="ehs-g"><?php
      $card($n($p_live), 'Took Live Typing', 'लाइव टाइपिंग दे रहे हैं', $n($p_live_today) . ' today');
      $card($n(max(0, $p_all - $p_live)), 'Did not take Live Typing', 'लाइव टाइपिंग नहीं दी', 'out of ' . $n($p_all) . ' test takers');
    ?></div>

    <h2>Emails of test takers <i>टेस्ट देने वालों के ईमेल</i></h2>
    <p class="ehs-note">Only logged-in users have an email. Guests type a name only, so they have no email here. / ईमेल सिर्फ लॉगिन करके टेस्ट देने वालों का होता है।</p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0 0 10px"><input type="hidden" name="action" value="eh_stats_csv"><?php wp_nonce_field('eh_stats_csv'); submit_button('Download all as CSV / CSV डाउनलोड', 'secondary', 'submit', false); ?></form>
    <table class="widefat striped"><thead><tr><th>#</th><th>Name / नाम</th><th>Email</th><th>Tests / टेस्ट</th><th>Live Typing</th><th>Last test / आखिरी टेस्ट</th></tr></thead><tbody>
      <?php if (!$takers): ?><tr><td colspan="6">No logged-in test takers yet. / अभी कोई नहीं।</td></tr><?php endif; ?>
      <?php foreach ($takers as $i => $r): ?>
        <tr><td><?php echo $i + 1; ?></td><td><?php echo esc_html($r['name']); ?></td><td><a href="mailto:<?php echo esc_attr($r['email']); ?>"><?php echo esc_html($r['email']); ?></a></td><td><?php echo esc_html($n($r['tests'])); ?></td><td><?php echo $r['live'] ? 'Yes / हाँ (' . esc_html($n($r['live'])) . ')' : 'No / नहीं'; ?></td><td><?php echo esc_html(wp_date('d M Y, H:i', strtotime($r['last']))); ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php if (count($takers) >= 500): ?><p class="ehs-note">Showing the latest 500. The CSV has everyone.</p><?php endif; ?>
    </div>
    <?php
}

add_action('admin_post_eh_stats_csv', function () {
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    check_admin_referer('eh_stats_csv');
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="test-takers-' . current_time('Y-m-d') . '.csv"');
    $o = fopen('php://output', 'w');
    fwrite($o, "\xEF\xBB\xBF");
    $safe = function ($s) { $s = (string) $s; return preg_match('/^[=+\-@\t\r]/', $s) ? "'" . $s : $s; };
    fputcsv($o, ['Name', 'Email', 'Tests', 'Live Typing tests', 'Last test']);
    foreach (eh_stats_takers() as $r) fputcsv($o, [$safe($r['name']), $safe($r['email']), $r['tests'], $r['live'], $r['last']]);
    fclose($o);
    exit;
});
