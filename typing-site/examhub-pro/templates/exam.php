<?php get_header();
$e = eh_exam(get_query_var('eh_slug')); $att = eh_attempt_counts(); $board = eh_leaderboard($e['slug'], '', 10);
$up = ($e['eval'] ?? '') === 'upsssc';
$min = (int) $e['minutes']; $wpm = (int) eh_target_wpm($e);
$kb = $e['lang'] === 'hi' ? 'Krutidev · Mangal Inscript · Remington Gail' : eh_lang_label($e['lang']);
$start = home_url('/typing/' . $e['slug'] . '/');
$used = number_format_i18n($att[$e['slug']] ?? 0); $tests = count(eh_passages($e['lang'] === 'en' ? 'en' : 'hi'));
if ($up) {
    $rules = [
        '<b>Words</b> = key strokes ÷ 5',
        '<b>First 5 mistakes free</b>, then 6 words cut for each mistake (the wrong word + 5)',
        '<b>Speed</b> = net words ÷ ' . $min . ' minutes · need <b>' . (int) $e['wpm'] . ' WPM</b>',
        'Wrong, missed, added, repeated or incomplete word = <b>full</b> mistake; spacing, punctuation, word order and paragraph slips = <b>half</b>' . ($e['lang'] === 'hi' ? '' : ' (capital/small letter slips too)'),
        'Backspace works only on the current and the previous word',
    ];
} else {
    $rules = [
        $e['unit'] === 'keys' ? 'In <b>' . $min . ' minutes</b> type at least <b>' . number_format_i18n($e['target']) . ' key depressions</b>' : 'In <b>' . $min . ' minutes</b> type about <b>' . number_format_i18n($e['target']) . ' words</b>',
        '<b>' . esc_html($e['allowed']) . '% errors</b> are allowed',
        'Wrong, missed or added word = <b>full</b> mistake; spacing, capital and punctuation slips = <b>half</b>',
    ];
    if (!empty($e['retype'])) $rules[] = 'Retyping exam: type the passage on screen as given';
}
$tiles = [
    ['🎯', $wpm . ' WPM', 'Target'],
    ['⏱', $min . ':00', 'Minutes'],
    ['⌨️', number_format_i18n($e['target']), $e['unit'] === 'keys' ? 'Keystrokes' : 'Words'],
    ['👥', $used, 'Students'],
    ['📚', $tests . '+', 'Tests'],
    ['⚡', $up ? '30/day' : '20/day', 'Live passages'],
];
$medal = ['🥇', '🥈', '🥉'];
?>
<section class="ex-hero"><div class="wrap ex-hero-in">
  <div class="ex-hero-l">
    <p class="ex-crumb"><a href="<?php echo esc_url(home_url('/exams/')); ?>">Exams</a> / <?php echo esc_html(eh_cat_label($e['cat'])); ?></p>
    <h1><?php echo esc_html($e['name']); ?></h1>
    <div class="ex-chips">
      <span>⏱ <?php echo $min; ?> min</span><span>🎯 <?php echo $wpm; ?> WPM</span><span>⌨️ <?php echo esc_html(eh_target_label($e)); ?></span><span><?php echo esc_html($kb); ?></span>
    </div>
  </div>
  <div class="ex-hero-r">
    <a class="ex-start" href="<?php echo esc_url($start); ?>">▶ Start Typing Test</a>
    <p><?php echo esc_html($used); ?> students · <?php echo (int) $tests; ?>+ tests</p>
    <a class="ex-explore" href="<?php echo esc_url(home_url('/exams/')); ?>">Explore all exams →</a>
  </div>
</div></section>
<section class="wrap ex-body">
  <div class="ex-col">
    <div class="ex-tiles">
      <?php foreach ($tiles as $t): ?><div class="ex-tile"><i><?php echo $t[0]; ?></i><b><?php echo esc_html($t[1]); ?></b><span><?php echo esc_html($t[2]); ?></span></div><?php endforeach; ?>
    </div>
    <h2 class="ex-h">📋 <?php echo $up ? 'UPSSSC Rule <small>(from the admit card)</small>' : 'Exam Rule'; ?></h2>
    <ol class="ex-rules">
      <?php foreach ($rules as $k => $r): ?><li><b class="ex-n"><?php echo $k + 1; ?></b><span><?php echo $r; ?></span></li><?php endforeach; ?>
    </ol>
    <?php if (in_array($e['lang'], ['mr', 'or'], true)): ?><p class="eh-tip">ℹ️ Passages in this language are coming soon. You can practise on English passages for now.</p><?php endif; ?>
    <a class="ex-start ex-start-sm" href="<?php echo esc_url($start); ?>">▶ Start Typing Test</a>
  </div>
  <div class="ex-col">
    <h2 class="ex-h">🏆 Top Results</h2>
    <div class="ex-board"><table>
      <?php if (!$board): ?><tr><td class="muted" style="padding:16px">No tests yet. Be the first!</td></tr><?php endif; ?>
      <?php foreach ($board as $i => $r): ?><tr>
        <td class="ex-rk"><?php echo $i < 3 ? $medal[$i] : $i + 1; ?></td>
        <td class="ex-nm"><?php echo esc_html(eh_display_name($r)); ?></td>
        <td class="ex-sp"><b><?php echo (int) $r['net']; ?></b> <small>WPM</small></td>
        <td class="ex-ac"><?php echo esc_html($r['acc']); ?>%</td>
        <td><?php echo $r['pass'] ? '<span class="pill ok">Pass</span>' : '<span class="pill no">Fail</span>'; ?></td>
      </tr><?php endforeach; ?>
    </table></div>
  </div>
</section>
<?php get_footer();
