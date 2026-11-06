<?php get_header();
$exam = sanitize_title($_GET['exam'] ?? '');
$board = eh_leaderboard($exam, '', 50, $exam ? 0 : 30);
$recent = eh_recent(20); ?>
<section class="eh-page-head slim"><div class="wrap">
  <h1>Exam Based Result</h1><p class="muted">एग्ज़ाम चुनिए और उसके टॉप रिज़ल्ट देखिए। अभी पिछले 30 दिन के टॉप रिज़ल्ट दिख रहे हैं।</p>
  <form class="eh-filters" method="get"><select name="exam" onchange="this.form.submit()"><option value="">All Exams</option>
  <?php foreach (eh_exams() as $e) echo '<option value="' . esc_attr($e['slug']) . '"' . selected($exam, $e['slug'], false) . '>' . esc_html($e['name']) . '</option>'; ?>
  </select></form>
</div></section>
<section class="wrap eh-section eh-two">
  <div><h2>Leaderboard</h2>
  <div class="eh-table-wrap"><table class="eh-table"><thead><tr><th>#</th><th>Name</th><th>Exam</th><th>Net</th><th>Acc.</th><th></th></tr></thead><tbody>
  <?php if (!$board): ?><tr><td colspan="6" class="muted">अभी कोई रिज़ल्ट नहीं।</td></tr><?php endif; ?>
  <?php foreach ($board as $i => $r): $e = eh_exam($r['exam']); ?><tr><td><?php echo $i + 1; ?></td><td><?php echo esc_html(eh_display_name($r)); ?></td><td class="small"><?php echo esc_html($e ? $e['name'] : 'Demo / Live'); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo esc_html($r['acc']); ?>%</td><td><?php echo $r['pass'] ? '<span class="pill ok">Pass</span>' : '<span class="pill no">Fail</span>'; ?></td></tr><?php endforeach; ?>
  </tbody></table></div></div>
  <div><h2>Recent Exams Taken</h2>
  <div class="eh-table-wrap"><table class="eh-table"><thead><tr><th>Name</th><th>Exam</th><th>Net</th><th>When</th></tr></thead><tbody>
  <?php foreach ($recent as $r): $e = eh_exam($r['exam']); ?><tr><td><?php echo esc_html(eh_display_name($r)); ?></td><td class="small"><?php echo esc_html($e ? $e['name'] : 'Demo / Live'); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td class="small"><?php echo esc_html(human_time_diff(strtotime($r['created']), current_time('timestamp'))); ?> ago</td></tr><?php endforeach; ?>
  </tbody></table></div></div>
</section>
<?php get_footer();
