<?php
global $wpdb; $t = eh_table();
$exam = sanitize_title($_GET['exam'] ?? '');
$size = in_array((int) ($_GET['size'] ?? 10), [10, 25, 50, 100], true) ? (int) $_GET['size'] : 25;
$sort = ($_GET['sort'] ?? '') === 'top' ? 'top' : 'latest';
$pg = max(1, (int) ($_GET['pg'] ?? 1));
$where = 'words > 0' . ($exam ? $wpdb->prepare(' AND exam = %s', $exam) : '') . ($sort === 'top' ? $wpdb->prepare(' AND created >= %s', gmdate('Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS)) : '');
$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE $where");
$rows = $wpdb->get_results("SELECT id, user_id, name, exam, passage, title, minutes, keys_count, words, full_m, half_m, gross, net, acc, pass, created FROM $t WHERE $where ORDER BY " . ($sort === 'top' ? 'net DESC, acc DESC' : 'id DESC') . " LIMIT $size OFFSET " . (($pg - 1) * $size), ARRAY_A);
$ps = []; foreach (eh_passages() as $p) $ps[$p['id']] = $p;
get_header(); ?>
<section class="wrap eh-section">
  <h1>Exam Based Typing Results</h1>
  <form class="eh-res-filters" method="get">
    <label>Page Size <select name="size" onchange="this.form.submit()"><?php foreach ([10, 25, 50, 100] as $z) echo '<option' . selected($size, $z, false) . '>' . $z . '</option>'; ?></select></label>
    <label>Exam <select name="exam" onchange="this.form.submit()"><option value="">All</option><?php foreach (eh_exams() as $e) echo '<option value="' . esc_attr($e['slug']) . '"' . selected($exam, $e['slug'], false) . '>' . esc_html($e['name']) . '</option>'; ?></select></label>
    <label>Sort <select name="sort" onchange="this.form.submit()"><option value="latest">Latest</option><option value="top" <?php selected($sort, 'top'); ?>>Top net speed (30 days)</option></select></label>
  </form>
  <div class="eh-table-wrap"><table class="eh-table eh-ebr"><thead><tr><th>Sl.</th><th>Name</th><th>Exam Taken</th><th>Passage Used</th><th>Typing Date</th><th>Time Taken</th><th>Key Strokes</th><th>Target</th><th>Gross</th><th>Net</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="10" class="muted">No results yet. <a href="<?php echo esc_url(home_url('/exams/')); ?>">take the first test</a>.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r):
      $e = eh_exam($r['exam']); $p = $ps[$r['passage']] ?? null;
      $tgtSpeed = eh_target_wpm($e); list($gw, $nw) = eh_row_speeds($r);
      $cg = !$tgtSpeed || $gw >= $tgtSpeed ? 'g' : 'r'; $cn = !$tgtSpeed || $nw >= $tgtSpeed ? 'g' : 'r'; ?>
    <tr>
      <td><?php echo ($pg - 1) * $size + $i + 1; ?></td>
      <td><b><?php echo esc_html(eh_display_name($r)); ?></b></td>
      <td class="small"><?php echo esc_html($e ? $e['name'] : 'Demo / Live Typing'); ?><?php if ($e) echo ' - ' . (int) $e['minutes'] . ':00 min.'; ?></td>
      <td class="small"><?php echo esc_html($r['title'] ?: ($p['title'] ?? '—')); ?><?php if ($p) echo ' - ' . number_format_i18n($p['chars']) . ' key strokes'; ?></td>
      <td class="small"><?php echo esc_html(mysql2date('d/m/Y', $r['created'])); ?></td>
      <td class="small"><?php echo esc_html(gmdate('i:s', (int) round($r['minutes'] * 60))); ?> min.</td>
      <td><?php echo (int) $r['keys_count']; ?></td>
      <td><b><?php echo $tgtSpeed ?: '—'; ?></b></td>
      <td class="<?php echo $cg; ?>"><b><?php echo esc_html($gw); ?></b></td>
      <td class="<?php echo $cn; ?>"><b><?php echo esc_html($nw); ?></b></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php if ($total > $size): ?><div class="eh-pager"><?php $pages = (int) ceil($total / $size); for ($k = max(1, $pg - 4); $k <= min($pages, $pg + 4); $k++) echo '<a class="' . ($k === $pg ? 'on' : '') . '" href="' . esc_url(add_query_arg('pg', $k)) . '">' . $k . '</a>'; ?></div><?php endif; ?>
  <p class="muted small">Target = speed (WPM) needed to qualify. Green = above target, red = below target.</p>
</section>
<?php get_footer();
