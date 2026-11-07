<?php
if (!is_user_logged_in()) { wp_safe_redirect(eh_login_page_url(home_url('/typing-insight/'))); exit; }
global $wpdb; $uid = get_current_user_id(); $t = eh_table();
$days = in_array((int) ($_GET['d'] ?? 0), [1, 2, 7, 15, 30], true) ? (int) $_GET['d'] : 0;
$rows = [];
if ($days) {
    $since = wp_date('Y-m-d 00:00:00', current_time('timestamp') - ($days - 1) * DAY_IN_SECONDS);
    $rows = $wpdb->get_results($wpdb->prepare("SELECT exam, keys_count, words, gross, net, pass, minutes, mist, created FROM $t WHERE user_id = %d AND created >= %s ORDER BY id ASC", $uid, $since), ARRAY_A);
}
$n = count($rows); $sumG = $sumN = 0; $bestG = $bestN = 0; $bestNG = 0; $hits = 0;
$byDay = []; $exams = []; $added = []; $deleted = []; $miss = [];
foreach ($rows as $r) {
    $e = eh_exam($r['exam']); $d = substr($r['created'], 0, 10);
    $hit = $e ? (($e['unit'] === 'keys' ? (int) $r['keys_count'] : (int) $r['words']) >= (int) $e['target']) : false;
    $sumG += $r['gross']; $sumN += $r['net']; $hits += $hit ? 1 : 0;
    if ($r['gross'] > $bestG) $bestG = (int) $r['gross'];
    if ($r['net'] > $bestN) { $bestN = (int) $r['net']; $bestNG = (int) $r['gross']; }
    $byDay[$d] = $byDay[$d] ?? ['g' => 0, 'n' => 0, 'h' => 0];
    $byDay[$d]['g'] = max($byDay[$d]['g'], (int) $r['gross']); $byDay[$d]['n'] = max($byDay[$d]['n'], (int) $r['net']); $byDay[$d]['h'] += $hit ? 1 : 0;
    $exams[$r['exam']] = ['e' => $e, 'last' => $r['created'], 'min' => $r['minutes']];
    foreach ((array) json_decode((string) $r['mist'], true) as $m) {
        if (!is_array($m) || count($m) !== 2) continue;
        $k = $m[0] . ' → ' . $m[1]; $miss[$k] = ($miss[$k] ?? 0) + 1;
        $a = preg_split('//u', mb_strtolower($m[0]), -1, PREG_SPLIT_NO_EMPTY); $b = preg_split('//u', mb_strtolower($m[1]), -1, PREG_SPLIT_NO_EMPTY);
        $ca = array_count_values($a); $cb = array_count_values($b);
        foreach ($ca as $ch => $c) if ($c > ($cb[$ch] ?? 0) && trim($ch) !== '') $added[$ch] = ($added[$ch] ?? 0) + $c - ($cb[$ch] ?? 0);
        foreach ($cb as $ch => $c) if ($c > ($ca[$ch] ?? 0) && trim($ch) !== '') $deleted[$ch] = ($deleted[$ch] ?? 0) + $c - ($ca[$ch] ?? 0);
    }
}
arsort($added); arsort($deleted); arsort($miss);
/* fill every day of the interval so charts are continuous */
$series = [];
for ($i = $days - 1; $i >= 0; $i--) { $d = wp_date('Y-m-d', current_time('timestamp') - $i * DAY_IN_SECONDS); $series[$d] = $byDay[$d] ?? ['g' => 0, 'n' => 0, 'h' => 0]; }
function eh_spark($vals, $color) {
    $w = 360; $h = 150; $pad = 26; $cnt = count($vals); if (!$cnt) return '';
    $mx = max(1, max($vals)); $mx = ceil($mx / 4) * 4;
    $x = function ($i) use ($cnt, $w, $pad) { return $cnt === 1 ? $w / 2 : $pad + $i * ($w - $pad - 8) / ($cnt - 1); };
    $y = function ($v) use ($mx, $h) { return 8 + ($h - 30) * (1 - $v / $mx); };
    $s = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" class="eh-spark" role="img">';
    for ($g = 0; $g <= 4; $g++) { $gv = $mx * $g / 4; $s .= '<line x1="' . $pad . '" x2="' . ($w - 4) . '" y1="' . $y($gv) . '" y2="' . $y($gv) . '" class="gl"/><text x="' . ($pad - 4) . '" y="' . ($y($gv) + 4) . '" text-anchor="end">' . round($gv) . '</text>'; }
    $pts = []; foreach (array_values($vals) as $i => $v) $pts[] = round($x($i), 1) . ',' . round($y($v), 1);
    $s .= '<polyline points="' . implode(' ', $pts) . '" fill="none" stroke="' . $color . '" stroke-width="2.5"/>';
    foreach (array_values($vals) as $i => $v) $s .= '<circle cx="' . round($x($i), 1) . '" cy="' . round($y($v), 1) . '" r="3.2" fill="' . $color . '"><title>' . $v . '</title></circle>';
    $keys = array_keys($vals); $step = max(1, (int) ceil($cnt / 7));
    foreach ($keys as $i => $k) if ($i % $step === 0) $s .= '<text x="' . round($x($i), 1) . '" y="' . ($h - 6) . '" text-anchor="middle">' . esc_html(wp_date('j M', strtotime($k . ' 12:00'))) . '</text>';
    return $s . '</svg>';
}
get_header(); ?>
<?php echo eh_member_nav('insight'); ?>
<section class="eh-insight">
  <div class="wrap">
    <div class="eh-ins-top"><h1>Typing Progress Insight</h1>
      <form method="get"><select name="d" onchange="this.form.submit()"><option value="">— Pick a time range —</option><?php foreach ([1 => 'Last 1 day', 2 => 'Last 2 days', 7 => 'Last 7 days', 15 => 'Last 15 days', 30 => 'Last 30 days'] as $k => $l) echo '<option value="' . $k . '"' . selected($days, $k, false) . '>' . esc_html($l) . '</option>'; ?></select></form></div>
    <?php if (!$days): ?>
      <div class="eh-ins-empty">🕒<p>Pick a time range above to see your progress.</p></div>
    <?php elseif (!$n): ?>
      <div class="eh-ins-empty">📭<p>No tests in this period. <a href="<?php echo esc_url(home_url('/exams/')); ?>">Take a test →</a></p></div>
    <?php else: ?>
      <div class="eh-ins-cards">
        <div><span>Typed Passages</span><b class="c1"><?php echo $n; ?></b></div>
        <div><span>Target met</span><b class="c2"><?php echo $hits; ?></b></div>
        <div><span>Avg Gross Speed</span><b class="c3"><?php echo round($sumG / $n, 2); ?> wpm</b></div>
        <div><span>Avg Net Speed</span><b class="c4"><?php echo round($sumN / $n, 2); ?> wpm</b></div>
        <div class="w"><span>Best Gross Speed</span><b class="c5"><?php echo $bestG; ?> wpm</b></div>
        <div class="w"><span>Best Net Speed</span><b class="c5"><?php echo $bestN; ?> wpm</b><small>Gross: <?php echo $bestNG; ?> wpm</small></div>
      </div>
      <div class="eh-ins-charts">
        <div><h3>Best gross speed per day</h3><?php echo eh_spark(wp_list_pluck($series, 'g'), '#22b8cf'); ?></div>
        <div><h3>Best net speed per day</h3><?php echo eh_spark(wp_list_pluck($series, 'n'), '#40c057'); ?></div>
        <div><h3>Targets met per day</h3><?php echo eh_spark(wp_list_pluck($series, 'h'), '#fd7e14'); ?></div>
      </div>
      <h3 class="eh-ins-sub">Exams you practised</h3>
      <div class="eh-ins-exams">
        <?php $ci = 0; foreach ($exams as $slug => $x): ?><a href="<?php echo esc_url(home_url('/typing/' . $slug . '/')); ?>"><span><?php echo esc_html($x['e'] ? $x['e']['name'] : 'Demo Typing'); ?></span><b class="c<?php echo 1 + ($ci++ % 5); ?>"><?php echo esc_html(mysql2date('D d M Y', $x['last'])); ?></b><small>🎯 <?php echo $x['e'] ? esc_html(eh_target_label($x['e'])) : '—'; ?> · ⏱ <?php echo $x['e'] ? (int) $x['e']['minutes'] : 5; ?> min</small></a><?php endforeach; ?>
      </div>
      <div class="eh-ins-lists">
        <div><h3 class="g">⊕ Most added letters</h3><ul><?php foreach (array_slice($added, 0, 30, true) as $k => $v) echo '<li>' . esc_html($k) . ': ' . (int) $v . '</li>'; if (!$added) echo '<li class="muted">Nothing</li>'; ?></ul></div>
        <div><h3 class="r">⊖ Most missed letters</h3><ul><?php foreach (array_slice($deleted, 0, 30, true) as $k => $v) echo '<li>' . esc_html($k) . ': ' . (int) $v . '</li>'; if (!$deleted) echo '<li class="muted">Nothing</li>'; ?></ul></div>
        <div><h3 class="y">✎ Misspelt words</h3><ul><?php foreach (array_slice($miss, 0, 40, true) as $k => $v) echo '<li>' . esc_html($k) . ' (' . (int) $v . 'x)</li>'; if (!$miss) echo '<li class="muted">Nothing</li>'; ?></ul></div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php get_footer();
