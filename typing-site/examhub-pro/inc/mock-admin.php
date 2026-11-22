<?php
/* WP-admin panel for the Super TET live mock: summary, merit list with percentile, all attempts, question analysis, CSV. */
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page('Super TET Mock', 'Super TET Mock', 'manage_options', 'eh-stm', 'eh_stm_admin_page', 'dashicons-welcome-learn-more', 3);
});

add_action('admin_post_eh_stm_csv', function () {
    if (!current_user_can('manage_options')) wp_die('No');
    check_admin_referer('eh_stm_csv');
    global $wpdb; $r = eh_stm_round(sanitize_key($_GET['round'] ?? '')); eh_stm_expire($r);
    $what = sanitize_key($_GET['what'] ?? 'merit');
    $t = eh_stm_table();
    $rows = $what === 'all'
        ? $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE round = %s ORDER BY uid, att", $r['id']), ARRAY_A)
        : $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE round = %s AND submitted > 0 AND counted = 1 ORDER BY score DESC, (submitted - started) ASC", $r['id']), ARRAY_A);
    $n = count($rows); $tz = new DateTimeZone('Asia/Kolkata');
    $f = function ($x) use ($tz) { return $x ? (new DateTime('@' . $x))->setTimezone($tz)->format('d-m-Y H:i:s') : ''; };
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="super-tet-mock-' . $r['id'] . '-' . $what . '.csv"');
    echo "\xEF\xBB\xBF";
    $o = fopen('php://output', 'w');
    fputcsv($o, ['Rank', 'Name', 'Email', 'Roll No', 'Attempt', 'Status', 'Score', 'Correct', 'Wrong', 'Answered', 'Percentile', 'Time (min)', 'Tab switches', 'Device', 'Started', 'Submitted']);
    $scores = array_map(function ($x) { return (int) $x['score']; }, array_filter($rows, function ($x) { return $x['submitted'] && $x['counted']; }));
    $m = count($scores);
    foreach ($rows as $i => $x) {
        $done = (int) $x['submitted'] > 0; $s = (int) $x['score'];
        $le = $m ? count(array_filter($scores, function ($v) use ($s) { return $v <= $s; })) : 0;
        $rk = $m ? count(array_filter($scores, function ($v) use ($s) { return $v > $s; })) + 1 : '';
        fputcsv($o, [$done && $x['counted'] ? $rk : '', $x['name'], $x['email'], eh_stm_roll((int) $x['uid']), $x['att'], $done ? ($x['counted'] ? 'Submitted (counted)' : 'Submitted (old)') : ($x['started'] ? 'Running' : 'Login only'),
            $done ? $s : '', $done ? $x['correct'] : '', $done ? $x['wrong'] : '', $x['answered'], $done && $x['counted'] && $m ? round($le * 100 / $m, 2) : '',
            $done ? round(($x['submitted'] - $x['started']) / 60, 1) : '', $x['tabs'], $x['dev'], $f((int) $x['started']), $f((int) $x['submitted'])]);
    }
    fclose($o); exit;
});

add_action('admin_post_eh_stm_reset', function () {
    if (!current_user_can('manage_options')) wp_die('No');
    check_admin_referer('eh_stm_reset');
    global $wpdb; $wpdb->delete(eh_stm_table(), ['id' => (int) ($_GET['id'] ?? 0)]);
    wp_safe_redirect(admin_url('admin.php?page=eh-stm&round=' . sanitize_key($_GET['round'] ?? '') . '&tab=all')); exit;
});

function eh_stm_admin_page() {
    if (!current_user_can('manage_options')) return;
    global $wpdb; $t = eh_stm_table();
    $r = eh_stm_round(sanitize_key($_GET['round'] ?? '')); eh_stm_expire($r);
    $tab = sanitize_key($_GET['tab'] ?? 'merit'); $now = time();
    $tz = new DateTimeZone('Asia/Kolkata'); $f = function ($x, $fmt = 'd M, h:i A') use ($tz) { return $x ? (new DateTime('@' . $x))->setTimezone($tz)->format($fmt) : '—'; };
    $all = $wpdb->get_results($wpdb->prepare("SELECT id, uid, att, counted, name, email, seen, started, deadline, last, submitted, answered, tabs, correct, wrong, score, dev, ans FROM $t WHERE round = %s", $r['id']), ARRAY_A);
    $users = count(array_unique(array_column($all, 'uid')));
    $started = array_filter($all, function ($x) { return $x['started'] > 0; });
    $subs = array_filter($all, function ($x) { return $x['submitted'] > 0; });
    $merit = array_values(array_filter($all, function ($x) { return $x['submitted'] > 0 && $x['counted']; }));
    usort($merit, function ($a, $b) { return $b['score'] <=> $a['score'] ?: (($a['submitted'] - $a['started']) <=> ($b['submitted'] - $b['started'])); });
    $live = array_filter($all, function ($x) use ($now) { return $x['started'] && !$x['submitted'] && $now - $x['last'] < 120; });
    $sc = array_map('intval', array_column($merit, 'score')); $m = count($sc);
    sort($sc); $med = $m ? ($m % 2 ? $sc[intdiv($m, 2)] : ($sc[$m / 2 - 1] + $sc[$m / 2]) / 2) : 0;
    $avg = $m ? round(array_sum($sc) / $m, 1) : 0; $mx = $m ? max($sc) : 0; $mn = $m ? min($sc) : 0;
    $attc = []; foreach ($all as $x) if ($x['started']) $attc[$x['uid']] = ($attc[$x['uid']] ?? 0) + 1;
    $st = eh_stm_status($r);
    $stl = ['soon' => ['अभी खुला नहीं', '#64748b'], 'login' => ['लॉग-इन खुला', '#b45309'], 'live' => ['LIVE', '#16a34a'], 'over' => ['बंद', '#dc2626']][$st];
    $base = admin_url('admin.php?page=eh-stm&round=' . $r['id']);
    ?>
<div class="wrap eh-stm-adm">
<style>
.eh-stm-adm h1{display:flex;align-items:center;gap:10px}.eh-stm-adm .pill{font-size:12px;font-weight:700;color:#fff;border-radius:20px;padding:3px 10px}
.eh-k{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin:14px 0}
.eh-k div{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:12px 14px}.eh-k b{display:block;font-size:24px;color:#0f2a4d;line-height:1.2}.eh-k span{color:#646970;font-size:12.5px}
.eh-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:14px 16px;margin:14px 0}
.eh-card h2{margin:0 0 10px;font-size:15px}
.eh-bars{display:flex;align-items:flex-end;gap:5px;height:150px;border-bottom:1px solid #dcdcde;overflow-x:auto;padding-top:16px}
.eh-bars div{flex:0 0 36px;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;height:100%;font-size:11px}
.eh-bars i{display:block;width:100%;background:#2271b1;border-radius:4px 4px 0 0;min-height:2px}.eh-bars em{font-style:normal;color:#646970;margin-top:4px}
.eh-stm-adm table.widefat td,.eh-stm-adm table.widefat th{vertical-align:middle}
.eh-q{display:grid;grid-template-columns:repeat(auto-fill,minmax(70px,1fr));gap:6px}.eh-q div{border-radius:8px;padding:6px;text-align:center;font-size:12px;color:#fff}
</style>
<h1>Super TET Mock — एडमिन <span class="pill" style="background:<?php echo esc_attr($stl[1]); ?>"><?php echo esc_html($stl[0]); ?></span></h1>
<form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><input type="hidden" name="page" value="eh-stm"><input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>">
 <select name="round" onchange="this.form.submit()"><?php foreach (eh_stm_rounds() as $x): ?><option value="<?php echo esc_attr($x['id']); ?>" <?php selected($x['id'], $r['id']); ?>><?php echo esc_html($x['name']); ?></option><?php endforeach; ?></select>
 <span style="color:#646970"><?php echo esc_html($f($r['start_ts']) . ' → ' . $f($r['end_ts'])); ?></span>
 <a class="button" href="<?php echo esc_url(home_url('/super-tet-mock/?admin=1')); ?>" target="_blank">लाइव मॉनिटर ↗</a>
 <a class="button" href="<?php echo esc_url(home_url('/super-tet-mock/')); ?>" target="_blank">मॉक पेज ↗</a>
 <a class="button" href="<?php echo esc_url($base . '&tab=' . $tab); ?>">↻ Refresh</a>
</form>
<div class="eh-k">
 <div><b><?php echo $users; ?></b><span>लॉग-इन करके पेज खोला</span></div>
 <div><b><?php echo count($live); ?></b><span>अभी पेपर दे रहे</span></div>
 <div><b><?php echo count($attc); ?></b><span>पेपर शुरू किया (उम्मीदवार)</span></div>
 <div><b><?php echo count($started); ?></b><span>कुल प्रयास</span></div>
 <div><b><?php echo $m; ?></b><span>मेरिट में (सबमिट)</span></div>
 <div><b><?php echo $mx; ?></b><span>सबसे ज़्यादा अंक</span></div>
 <div><b><?php echo $avg; ?></b><span>औसत अंक</span></div>
 <div><b><?php echo $med; ?></b><span>मीडियन</span></div>
 <div><b><?php echo $mn; ?></b><span>सबसे कम अंक</span></div>
 <div><b><?php echo $attc ? max($attc) : 0; ?></b><span>एक व्यक्ति के अधिकतम प्रयास</span></div>
</div>
<?php
$B = []; foreach ($merit as $x) { $b = (int) floor($x['score'] / 20) * 20; $B[$b] = ($B[$b] ?? 0) + 1; } ksort($B); $bm = $B ? max($B) : 1;
?>
<div class="eh-card"><h2>अंक वितरण (20 अंकों के समूह)</h2><div class="eh-bars"><?php if (!$B) echo '<p style="color:#646970">अभी कोई सबमिट नहीं</p>'; foreach ($B as $b => $c): ?><div title="<?php echo $b . ' से ' . ($b + 19) . ': ' . $c; ?>"><span><?php echo $c; ?></span><i style="height:<?php echo max(2, $c * 110 / $bm); ?>px"></i><em><?php echo $b; ?></em></div><?php endforeach; ?></div></div>
<h2 class="nav-tab-wrapper">
 <?php foreach (['merit' => 'मेरिट लिस्ट', 'all' => 'सभी प्रयास', 'live' => 'अभी दे रहे', 'qa' => 'प्रश्न-वार विश्लेषण'] as $k => $l): ?><a class="nav-tab <?php echo $tab === $k ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url($base . '&tab=' . $k); ?>"><?php echo esc_html($l); ?></a><?php endforeach; ?>
</h2>
<p><a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=eh_stm_csv&what=merit&round=' . $r['id']), 'eh_stm_csv')); ?>">⬇ मेरिट लिस्ट CSV</a>
 <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=eh_stm_csv&what=all&round=' . $r['id']), 'eh_stm_csv')); ?>">⬇ सभी प्रयास CSV</a></p>
<?php if ($tab === 'merit'): ?>
<table class="widefat striped"><thead><tr><th>रैंक</th><th>नाम</th><th>ईमेल</th><th>रोल नं.</th><th>अंक</th><th>सही</th><th>गलत</th><th>परसेंटाइल</th><th>समय</th><th>प्रयास</th><th>टैब</th><th>डिवाइस</th><th>सबमिट</th></tr></thead><tbody>
<?php if (!$merit) echo '<tr><td colspan="13">अभी कोई नहीं</td></tr>'; foreach ($merit as $x): $s = (int) $x['score'];
 $rk = count(array_filter($sc, function ($v) use ($s) { return $v > $s; })) + 1; $pc = round(count(array_filter($sc, function ($v) use ($s) { return $v <= $s; })) * 100 / $m, 2); ?>
<tr><td><b><?php echo $rk; ?></b></td><td><?php echo esc_html($x['name']); ?></td><td><?php echo esc_html($x['email']); ?></td><td><?php echo esc_html(eh_stm_roll((int) $x['uid'])); ?></td><td><b><?php echo $s; ?></b></td><td><?php echo (int) $x['correct']; ?></td><td><?php echo (int) $x['wrong']; ?></td><td><?php echo $pc; ?></td><td><?php echo round(($x['submitted'] - $x['started']) / 60); ?> मि</td><td><?php echo (int) ($attc[$x['uid']] ?? 1); ?></td><td><?php echo (int) $x['tabs']; ?></td><td><?php echo esc_html($x['dev']); ?></td><td><?php echo esc_html($f((int) $x['submitted'])); ?></td></tr>
<?php endforeach; ?></tbody></table>
<?php elseif ($tab === 'all' || $tab === 'live'):
 $rows = $tab === 'live' ? $live : $all; usort($rows, function ($a, $b) { return $b['last'] <=> $a['last']; }); ?>
<table class="widefat striped"><thead><tr><th>नाम</th><th>ईमेल</th><th>प्रयास</th><th>स्थिति</th><th>किए</th><th>अंक</th><th>शुरू</th><th>बचा समय</th><th>आखिरी बार</th><th>टैब</th><th>डिवाइस</th><th></th></tr></thead><tbody>
<?php if (!$rows) echo '<tr><td colspan="12">कोई नहीं</td></tr>'; foreach ($rows as $x):
 $stx = $x['submitted'] ? ($x['counted'] ? '✅ सबमिट (गिना गया)' : 'सबमिट (पुराना)') : ($x['started'] ? ($now - $x['last'] < 120 ? '🟢 दे रहे' : '⏸ रुके') : 'केवल लॉग-इन'); ?>
<tr><td><?php echo esc_html($x['name']); ?></td><td><?php echo esc_html($x['email']); ?></td><td><?php echo (int) $x['att']; ?></td><td><?php echo esc_html($stx); ?></td><td><?php echo (int) $x['answered']; ?></td><td><?php echo $x['submitted'] ? (int) $x['score'] : '—'; ?></td><td><?php echo esc_html($f((int) $x['started'])); ?></td>
<td><?php echo $x['started'] && !$x['submitted'] ? esc_html(gmdate('G:i:s', max(0, $x['deadline'] - $now))) : '—'; ?></td><td><?php echo esc_html(human_time_diff((int) $x['last'], $now)); ?> पहले</td><td><?php echo (int) $x['tabs']; ?></td><td><?php echo esc_html($x['dev']); ?></td>
<td><a class="button button-small" onclick="return confirm('यह प्रयास मिटा दें?')" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=eh_stm_reset&id=' . $x['id'] . '&round=' . $r['id']), 'eh_stm_reset')); ?>">रीसेट</a></td></tr>
<?php endforeach; ?></tbody></table>
<?php else:
 $Q = eh_stm_paper($r); $cnt = array_fill(0, count($Q), [0, 0]);
 foreach ($merit as $x) { $a = json_decode($x['ans'] ?: '{}', true) ?: []; foreach ($a as $i => $v) { if (!isset($Q[$i])) continue; $cnt[$i][$v === $Q[$i]['a'] ? 0 : 1]++; } } ?>
<div class="eh-card"><h2>प्रश्न-वार सही % (मेरिट वाले <?php echo $m; ?> उम्मीदवारों में) — लाल = कठिन, हरा = आसान</h2><div class="eh-q">
<?php foreach ($Q as $i => $q): $c = $cnt[$i][0]; $w = $cnt[$i][1]; $p = $m ? round($c * 100 / $m) : 0; $col = $p >= 60 ? '#16a34a' : ($p >= 35 ? '#d97706' : '#dc2626'); ?>
<div style="background:<?php echo $col; ?>" title="<?php echo esc_attr(mb_substr($q['q'], 0, 120)); ?>"><b>Q<?php echo $i + 1; ?></b><br><?php echo $p; ?>% सही<br><small><?php echo $c; ?>✓ <?php echo $w; ?>✗</small></div>
<?php endforeach; ?></div></div>
<?php endif; ?>
</div>
<?php }
