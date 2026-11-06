<?php
if (!is_user_logged_in()) { wp_safe_redirect(wp_login_url(home_url('/my-dashboard/'))); exit; }
get_header(); global $wpdb; $uid = get_current_user_id();
$rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . eh_table() . ' WHERE user_id = %d ORDER BY id DESC LIMIT 100', $uid), ARRAY_A);
$best = 0; $avg = 0; $pass = 0;
foreach ($rows as $r) { $best = max($best, (int) $r['net']); $avg += (int) $r['net']; $pass += (int) $r['pass']; }
$avg = $rows ? round($avg / count($rows)) : 0; ?>
<section class="eh-page-head slim"><div class="wrap"><h1>नमस्ते, <?php echo esc_html(wp_get_current_user()->display_name); ?> 👋</h1><p class="muted">आपकी टाइपिंग प्रोग्रेस</p></div></section>
<section class="wrap eh-section">
  <div class="eh-res-grid">
    <div class="eh-stat"><span>Tests</span><b><?php echo count($rows); ?></b></div>
    <div class="eh-stat"><span>Best Net</span><b><?php echo $best; ?> WPM</b></div>
    <div class="eh-stat"><span>Average Net</span><b><?php echo $avg; ?> WPM</b></div>
    <div class="eh-stat"><span>Qualified</span><b><?php echo $pass; ?></b></div>
  </div>
  <h2>Result History</h2>
  <div class="eh-table-wrap"><table class="eh-table"><thead><tr><th>Date</th><th>Exam</th><th>Net</th><th>Gross</th><th>Acc.</th><th>Keys</th><th>Full/Half</th><th></th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="8" class="muted">अभी कोई टेस्ट नहीं दिया। <a href="<?php echo esc_url(home_url('/exams/')); ?>">पहला टेस्ट दें →</a></td></tr><?php endif; ?>
  <?php foreach ($rows as $r): $e = eh_exam($r['exam']); ?><tr><td class="small"><?php echo esc_html(mysql2date('d M, H:i', $r['created'])); ?></td><td class="small"><?php echo esc_html($e ? $e['name'] : ($r['live_date'] ? 'Live Typing' : 'Demo')); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo (int) $r['gross']; ?></td><td><?php echo esc_html($r['acc']); ?>%</td><td><?php echo (int) $r['keys_count']; ?></td><td><?php echo (int) $r['full_m'] . '/' . (int) $r['half_m']; ?></td><td><?php echo $r['pass'] ? '<span class="pill ok">Pass</span>' : '<span class="pill no">Fail</span>'; ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
</section>
<?php get_footer();
