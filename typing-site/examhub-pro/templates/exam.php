<?php get_header();
$e = eh_exam(get_query_var('eh_slug')); $att = eh_attempt_counts(); $board = eh_leaderboard($e['slug'], '', 10);
$rule = $e['unit'] === 'keys'
  ? 'In ' . (int) $e['minutes'] . ' minutes you need at least ' . number_format_i18n($e['target']) . ' key depressions.'
  : 'In ' . (int) $e['minutes'] . ' minutes you need to type about ' . number_format_i18n($e['target']) . ' words.';
?>
<section class="eh-page-head"><div class="wrap eh-exam-head">
  <div>
    <p class="muted small"><a href="<?php echo esc_url(home_url('/exams/')); ?>">Exams</a> / <?php echo esc_html(eh_cat_label($e['cat'])); ?></p>
    <h1><?php echo esc_html($e['name']); ?></h1>
  </div>
  <div class="eh-exam-head-r"><a class="eh-explore" href="<?php echo esc_url(home_url('/exams/')); ?>">Explore all exams →</a>
  <a class="btn dark lg" href="<?php echo esc_url(home_url('/typing/' . $e['slug'] . '/')); ?>">Start Typing Test →</a></div>
</div></section>
<section class="wrap eh-section eh-two">
  <div>
    <h2>About</h2>
    <?php if (($e['eval'] ?? '') === 'upsssc'): ?>
    <p>UPSSSC rule (from the admit card): in <?php echo (int) $e['minutes']; ?> minutes you need a net speed of at least <b><?php echo (int) $e['wpm']; ?> WPM</b>. Words = key depressions ÷ 5. The <b>first 5 mistakes are free</b>; after that <b>6 words are cut for each mistake</b> (the wrong word + 5). Speed = (words − cut words) ÷ <?php echo (int) $e['minutes']; ?> minutes. Wrong, missed, added, repeated or incomplete words are full mistakes; spacing, punctuation, word order and paragraph slips are half mistakes<?php echo $e['lang'] === 'hi' ? '' : ', and so are capital/small letter slips'; ?>. Backspace works only on the current and the previous word.</p>
    <?php else: ?>
    <p><?php echo esc_html($rule); ?> <?php echo esc_html($e['allowed']); ?>% errors are allowed. Wrong, missed or added words are full mistakes; spacing, capital and punctuation slips are half mistakes.<?php if (!empty($e['retype'])) echo ' This is a retyping exam: type the passage on screen as given.'; ?></p>
    <?php endif; ?>
    <div class="eh-info">
      <div><span>Languages</span><b><?php echo $e['lang'] === 'hi' ? 'Hindi - Krutidev, Hindi - Mangal Inscript, Hindi - Remington Gail' : esc_html(eh_lang_label($e['lang'])); ?></b></div>
      <div><span>Target Speed</span><b><?php echo (int) eh_target_wpm($e); ?> WPM</b></div>
      <div><span><?php echo $e['unit'] === 'keys' ? 'Keystrokes' : 'Words'; ?></span><b><?php echo esc_html(eh_target_label($e)); ?></b></div>
      <div><span>Time duration</span><b><?php echo (int) $e['minutes']; ?>:00 min</b></div>
      <div><span>Students using this exam</span><b><?php echo number_format_i18n($att[$e['slug']] ?? 0); ?> times</b></div>
      <div><span>Total Tests</span><b><?php echo count(eh_passages($e['lang'] === 'en' ? 'en' : 'hi')); ?>+ Tests</b></div>
      <div><span>Live Passages</span><b><?php echo ($e['eval'] ?? '') === 'upsssc' ? '30 new passages daily (10 Easy, 10 Moderate, 10 Hard)' : '20 new passages daily'; ?></b></div>
    </div>
    <?php if (in_array($e['lang'], ['mr', 'or'], true)): ?><p class="eh-tip">ℹ️ Passages in this language are coming soon. You can practise on English passages for now.</p><?php endif; ?>
    <a class="btn dark lg" href="<?php echo esc_url(home_url('/typing/' . $e['slug'] . '/')); ?>">Start Typing Test →</a>
  </div>
  <div>
    <h2>Top Results</h2>
    <div class="eh-table-wrap"><table class="eh-table"><thead><tr><th>#</th><th>Name</th><th>Net</th><th>Acc.</th><th></th></tr></thead><tbody>
    <?php if (!$board): ?><tr><td colspan="5" class="muted">No tests yet.</td></tr><?php endif; ?>
    <?php foreach ($board as $i => $r): ?><tr><td><?php echo $i + 1; ?></td><td><?php echo esc_html(eh_display_name($r)); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo esc_html($r['acc']); ?>%</td><td><?php echo $r['pass'] ? '<span class="pill ok">Pass</span>' : '<span class="pill no">Fail</span>'; ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
</section>
<?php get_footer();
