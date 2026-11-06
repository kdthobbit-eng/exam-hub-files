<?php get_header();
$L = eh_live_info();
$eh_exam = ['slug' => 'demo', 'name' => 'Daily Live Typing — ' . wp_date('d M Y'), 'lang' => $L['lang'], 'unit' => 'words', 'target' => $L['lang'] === 'hi' ? 250 : 300, 'minutes' => $L['minutes'], 'cat' => 'other', 'allowed' => (float) eh_opt('allowed_error'), 'retype' => false];
$eh_cfg = ['fixedPassage' => $L['passage'], 'lockTime' => true, 'live' => $L['date']];
$board = eh_leaderboard('', $L['date'], 25);
?>
<section class="eh-page-head slim"><div class="wrap">
  <h1>🏆 Daily Live Typing</h1>
  <p class="muted">आज का पैसेज (<?php echo $L['lang'] === 'hi' ? 'Hindi' : 'English'; ?>, <?php echo (int) $L['minutes']; ?> मिनट) सबके लिए एक जैसा है। टेस्ट दें और आज की रैंक देखें — हर रात 12 बजे नया पैसेज।</p>
</div></section>
<section class="wrap eh-section eh-live">
  <div><?php include __DIR__ . '/_test-ui.php'; ?></div>
  <aside class="eh-panel">
    <h2>आज की रैंकिंग</h2>
    <table class="eh-table"><thead><tr><th>#</th><th>Name</th><th>Net</th><th>Acc.</th></tr></thead><tbody>
    <?php if (!$board): ?><tr><td colspan="4" class="muted">आज की पहली रैंक आपकी हो सकती है!</td></tr><?php endif; ?>
    <?php foreach ($board as $i => $r): ?><tr><td><?php echo $i + 1; ?></td><td><?php echo esc_html(eh_display_name($r)); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo esc_html($r['acc']); ?>%</td></tr><?php endforeach; ?>
    </tbody></table>
  </aside>
</section>
<?php get_footer();
