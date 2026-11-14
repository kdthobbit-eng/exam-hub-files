<?php
if (!is_user_logged_in()) { wp_safe_redirect(eh_login_page_url(home_url('/my-dashboard/'))); exit; }
global $wpdb; $uid = get_current_user_id(); $t = eh_table();
$agg = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) n, AVG(gross) g, AVG(net) nt, MAX(net) b, SUM(pass) q FROM $t WHERE user_id = %d", $uid), ARRAY_A);
$f = isset($_GET['f']) && in_array($_GET['f'], ['q', 'u'], true) ? $_GET['f'] : '';
$where = $wpdb->prepare('user_id = %d', $uid) . ($f === 'q' ? ' AND pass = 1' : ($f === 'u' ? ' AND pass = 0' : ''));
$per = 25; $pg = max(1, (int) ($_GET['pg'] ?? 1));
$cnt = (int) $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE $where");
$rows = $wpdb->get_results("SELECT * FROM $t WHERE $where ORDER BY id DESC LIMIT $per OFFSET " . (($pg - 1) * $per), ARRAY_A);
$n = (int) $agg['n']; $nq = (int) $agg['q'];
$ps = []; foreach (eh_passages() as $p) $ps[$p['id']] = $p;
get_header(); ?>
<?php echo eh_member_nav('history'); ?>
<section class="wrap eh-section">
  <div class="eh-hist-stats">
    <div><b><?php echo number_format_i18n($n); ?><?php echo $n ? ' +' : ''; ?></b><span>Tests taken</span></div>
    <div><b><?php echo (int) round((float) $agg['g']); ?> wpm</b><span>Avg. Gross Speed</span></div>
    <div><b><?php echo (int) round((float) $agg['nt']); ?> wpm</b><span>Avg. Net Speed</span></div>
    <div><b><?php echo (int) $agg['b']; ?> wpm</b><span>Best Net Speed</span></div>
  </div>
  <div class="eh-hist-box">
    <div class="eh-hist-bar">
      <h2>Typing Attempt History</h2>
      <div class="eh-chips">
        <a class="<?php echo $f === '' ? 'on' : ''; ?>" href="<?php echo esc_url(remove_query_arg(['f', 'pg'])); ?>">All <i><?php echo $n; ?></i></a>
        <a class="<?php echo $f === 'q' ? 'on' : ''; ?>" href="<?php echo esc_url(add_query_arg('f', 'q', remove_query_arg('pg'))); ?>">Qualified <i><?php echo $nq; ?></i></a>
        <a class="<?php echo $f === 'u' ? 'on' : ''; ?>" href="<?php echo esc_url(add_query_arg('f', 'u', remove_query_arg('pg'))); ?>">Unqualified <i><?php echo $n - $nq; ?></i></a>
      </div>
      <details class="eh-cols"><summary>Columns ▾</summary><div id="eh-cols"></div></details>
    </div>
    <div class="eh-table-wrap"><table class="eh-table eh-hist" id="eh-hist"><thead><tr>
      <th>#</th><th>Exam &amp; Duration</th><th>Passage &amp; Given Keys</th><th>Date &amp; Time Used</th><th>Keys Typed</th><th>Target (WPM)</th><th>Gross (WPM)</th><th>Net (WPM)</th><th>Accuracy</th><th>Full/Half</th><th>Actions</th>
    </tr></thead><tbody>
    <?php if (!$rows): ?><tr><td colspan="11" class="muted">Nothing here yet. <a href="<?php echo esc_url(home_url('/exams/')); ?>">Take your first test →</a></td></tr><?php endif; ?>
    <?php foreach ($rows as $i => $r):
        $e = eh_exam($r['exam']); $p = $ps[$r['passage']] ?? null; $tw = eh_target_wpm($e); list($gw, $nw, $rpass, $rkeys) = eh_row_speeds($r, $e); $r['pass'] = $rpass;
        $retype = $r['exam'] === 'demo' ? home_url('/demo-typing/') : home_url('/typing/' . $r['exam'] . '/');
        if ($r['passage']) $retype = add_query_arg('passage', $r['passage'], $retype); ?>
      <tr>
        <td><?php echo ($pg - 1) * $per + $i + 1; ?></td>
        <td><b><?php echo esc_html($e ? $e['name'] : ($r['exam'] === 'demo' ? 'Demo Typing' : $r['exam'])); ?></b><br><span class="muted small"><?php echo $e ? (int) $e['minutes'] . ':00 min' : ''; ?><?php echo $r['live_date'] ? ' · Live' : ''; ?></span> <?php echo $r['pass'] ? '<span class="pill ok">Qualified</span>' : '<span class="pill no">Unqualified</span>'; ?></td>
        <td class="small"><?php echo esc_html($r['title'] ?: ($p['title'] ?? '—')); ?><br><span class="muted"><?php echo $e && $e['unit'] === 'keys' ? number_format_i18n($e['target']) . ' keystrokes given' : ($p ? number_format_i18n($p['chars']) . ' keystrokes' : ''); ?></span></td>
        <td class="small"><?php echo esc_html(mysql2date('d/m/Y', $r['created'])); ?><br><span class="muted">Used: <?php echo esc_html(gmdate('i:s', (int) round($r['minutes'] * 60))); ?> min</span></td>
        <td><?php echo (int) $rkeys; ?></td>
        <td><b><?php echo $tw ?: '—'; ?></b></td>
        <td class="<?php echo $tw && $gw >= $tw ? 'g' : 'r'; ?>"><b><?php echo esc_html($gw); ?></b></td>
        <td class="<?php echo $tw && $nw >= $tw ? 'g' : 'r'; ?>"><b><?php echo esc_html($nw); ?></b></td>
        <td><?php echo esc_html($r['acc']); ?>%</td>
        <td><?php echo (int) $r['full_m'] . '/' . (int) $r['half_m']; ?></td>
        <td class="eh-act"><?php if ($r['rkey']): ?><a class="btn ghost sm" href="<?php echo esc_url(eh_result_url($r['id'], $r['rkey'])); ?>">View</a><?php endif; ?><a class="btn dark sm" href="<?php echo esc_url($retype); ?>">Retype</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
    <?php if ($cnt > $per): ?><div class="eh-pager"><?php for ($k = 1; $k <= ceil($cnt / $per); $k++) echo '<a class="' . ($k === $pg ? 'on' : '') . '" href="' . esc_url(add_query_arg('pg', $k)) . '">' . $k . '</a>'; ?></div><?php endif; ?>
  </div>
</section>
<script>
(function(){var t=document.getElementById('eh-hist'),box=document.getElementById('eh-cols');if(!t||!box)return;
var hs=[].slice.call(t.tHead.rows[0].cells),off={8:true,9:true};try{var sv=localStorage.getItem('eh_cols2');if(sv)off=JSON.parse(sv)}catch(e){}
function apply(){[].slice.call(t.rows).forEach(function(r){[].slice.call(r.cells).forEach(function(c,i){if(r.cells.length===hs.length)c.style.display=off[i]?'none':''})})}
hs.forEach(function(h,i){if(i===0)return;var l=document.createElement('label');l.innerHTML='<input type="checkbox"'+(off[i]?'':' checked')+'> '+h.textContent;l.firstChild.onchange=function(){off[i]=!this.checked;try{localStorage.setItem('eh_cols2',JSON.stringify(off))}catch(e){}apply()};box.appendChild(l)});apply();})();
</script>
<?php get_footer();
