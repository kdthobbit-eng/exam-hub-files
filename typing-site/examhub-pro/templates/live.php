<?php
$S = eh_live_schedule();
$now = (int) wp_date('G') * 60 + (int) wp_date('i');
$sel = sanitize_title($_GET['exam'] ?? '');
$pid = sanitize_key($_GET['p'] ?? ''); $kb = sanitize_key($_GET['kb'] ?? '');
$row = null; foreach ($S as $r) if (!$row && $r['exam']['slug'] === $sel && ($pid === '' || ($r['passage']['id'] ?? '') === $pid)) $row = $r;
$medal = ['🥇', '🥈', '🥉'];
global $wpdb;
get_header();
echo eh_member_nav('live');
if ($row):
    $L = $row; $open = $now >= $L['s'] && $now <= $L['e'];
    $allday = !empty($L['allday']); $lp = sanitize_key($L['passage']['id'] ?? '');
    $board = eh_leaderboard($L['exam']['slug'], $L['date'], 25, 0, $lp);
    $players = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_table() . ' WHERE live_date = %s AND exam = %s AND passage = %s', $L['date'], $L['exam']['slug'], $lp));
?>
<section class="eh-live-hero"><div class="wrap eh-live-hero-in">
  <div>
    <p class="muted small"><a href="<?php echo esc_url(home_url('/live-typing/')); ?>">← All Live Typing tests</a></p>
    <span class="eh-live-dot"><?php echo $open ? 'LIVE' : ($now < $L['s'] ? 'Soon' : 'Closed'); ?></span>
    <h1><?php echo esc_html($L['exam']['name'] . (!empty($L['no']) ? ' · Passage ' . (int) $L['no'] : '')); ?></h1>
    <p class="lead"><?php echo $allday ? 'This passage is the same for everyone today and is open all day. Ranking is by net speed.' : 'Today’s passage is the same for everyone. Open from ' . esc_html($L['start'] . ' to ' . $L['end']) . '. Take the test in this window; ranking is by net speed.'; ?></p>
  </div>
  <div class="eh-live-cards">
    <div class="eh-lc"><span>Today’s passage</span><b><?php echo esc_html($L['passage']['title'] ?? '—'); ?></b><em><?php echo $L['lang'] === 'hi' ? 'Hindi' : 'English'; ?> · <?php echo esc_html(eh_target_label($L['exam'])); ?></em></div>
    <div class="eh-lc"><span>Tests today</span><b><?php echo number_format_i18n($players); ?></b><em>people</em></div>
    <div class="eh-lc dark"><span><?php echo $open ? ($allday ? 'Time left today' : 'Closes in') : ($now < $L['s'] ? 'Opens in' : 'Back tomorrow'); ?></span><b id="eh-cd" data-s="<?php echo $open ? ($L['e'] - $now) * 60 : ($now < $L['s'] ? ($L['s'] - $now) * 60 : 0); ?>">--:--:--</b><em><?php echo $allday ? 'Open all day' : esc_html($L['start'] . ' – ' . $L['end']); ?></em></div>
  </div>
</div></section>
<section class="wrap eh-section eh-live">
  <div><?php if ($open && $L['passage']):
      $eh_exam = $L['exam']; if ($eh_exam['lang'] !== 'hi') $eh_exam['lang'] = 'en';
      $eh_cfg = ['fixedPassage' => $L['passage']['id'], 'lockTime' => true, 'live' => $L['date']];
      if ($L['lang'] === 'hi' && in_array($kb, ['krutidev', 'inscript'], true)) $eh_cfg['layout'] = $kb;
      include __DIR__ . '/_test-ui.php';
    else: ?>
      <div class="eh-panel eh-live-closed"><h2><?php echo $now < $L['s'] ? 'Test opens at ' . esc_html($L['start']) : 'Today’s live test is over'; ?></h2>
      <p class="muted">Meanwhile, practise this exam.</p>
      <a class="btn dark" href="<?php echo esc_url(home_url('/typing/' . $L['exam']['slug'] . '/')); ?>">Practice Test →</a></div>
    <?php endif; ?></div>
  <aside>
    <div class="eh-panel eh-rank">
      <div class="eh-rank-head"><h2>Today’s ranking</h2><span class="muted small" id="eh-rk-time">Auto-updates</span></div>
      <div class="eh-podium" id="eh-podium">
        <?php for ($i = 0; $i < 3; $i++): $r = $board[$i] ?? null; ?>
          <div class="p p<?php echo $i + 1; ?>"><i><?php echo $medal[$i]; ?></i><b><?php echo $r ? esc_html(eh_display_name($r)) : '—'; ?></b><span><?php echo $r ? (int) $r['net'] . ' WPM' : 'Empty'; ?></span></div>
        <?php endfor; ?>
      </div>
      <ol class="eh-rk-list" id="eh-rk-list" start="4">
        <?php foreach (array_slice($board, 3) as $r): ?><li><span><?php echo esc_html(eh_display_name($r)); ?></span><b><?php echo (int) $r['net']; ?></b><em><?php echo esc_html($r['acc']); ?>%</em></li><?php endforeach; ?>
      </ol>
      <?php if (!$board): ?><p class="muted small" id="eh-rk-empty">No one has taken the test yet. The first rank can be yours.</p><?php endif; ?>
    </div>
  </aside>
</section>
<script>
(function(){
  var cd=document.getElementById('eh-cd'),s=+cd.dataset.s;
  function t(){if(s<=0){cd.textContent='00:00:00';return}var h=Math.floor(s/3600),m=Math.floor(s%3600/60),x=s%60;cd.textContent=[h,m,x].map(function(v){return(v<10?'0':'')+v}).join(':');s--;if(s===0)setTimeout(function(){location.reload()},1500)}
  t();setInterval(t,1000);
  var api='<?php echo esc_url_raw(add_query_arg(['date' => $L['date'], 'exam' => $L['exam']['slug'], 'passage' => $lp], rest_url('eh/v1/leaderboard'))); ?>',md=['🥇','🥈','🥉'];
  function esc(v){return String(v).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
  function load(){fetch(api,{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(rows){
    if(!Array.isArray(rows))return;
    document.getElementById('eh-podium').innerHTML=[0,1,2].map(function(i){var r=rows[i];return '<div class="p p'+(i+1)+'"><i>'+md[i]+'</i><b>'+(r?esc(r.name):'—')+'</b><span>'+(r?r.net+' WPM':'Empty')+'</span></div>'}).join('');
    document.getElementById('eh-rk-list').innerHTML=rows.slice(3,25).map(function(r){return '<li><span>'+esc(r.name)+'</span><b>'+r.net+'</b><em>'+r.acc+'%</em></li>'}).join('');
    var e=document.getElementById('eh-rk-empty');if(e&&rows.length)e.remove();
    document.getElementById('eh-rk-time').textContent='Updated: '+new Date().toLocaleTimeString('hi-IN',{hour:'2-digit',minute:'2-digit'});
  }).catch(function(){})}
  setInterval(load,30000);document.addEventListener('eh:saved',load);
})();
</script>
<?php else:
  $allday = $S && !empty($S[0]['allday']); ?>
<section class="wrap eh-section">
  <p class="muted small"><?php echo esc_html(wp_date('d/m/Y')); ?> · <?php echo esc_html(eh_opt('brand_typing') ?: eh_opt('brand')); ?></p>
  <?php if ($allday): ?>
  <h1 class="eh-live-h">UPSSSC Junior Assistant Live Typing</h1>
  <p class="muted"><?php echo count($S); ?> passages today: English and Hindi (Krutidev or Mangal Inscript). All are open the whole day. Everyone gets the same passage, and each passage has its own ranking by net speed.</p>
  <div class="eh-chips eh-live-chips"><button type="button" class="on" data-f="">All</button><button type="button" data-f="en">English</button><button type="button" data-f="hi">Hindi</button></div>
  <?php else: ?>
  <h1 class="eh-live-h">Live Typing — same passage, same time, for everyone</h1>
  <p class="muted">Each exam has a live test at a fixed time every day. Everyone gets the same passage and the day's ranking is by net speed.</p>
  <div class="eh-chips eh-live-chips"><button type="button" class="on" data-f="">All</button><button type="button" data-f="en">English</button><button type="button" data-f="hi">Hindi</button></div>
  <?php endif; ?>
  <div class="eh-table-wrap"><table class="eh-table eh-live-table"><thead><tr><th>#</th><th>Test</th><th>Passage</th><th>Language</th><th>Time</th><th>Status</th><th>Start</th></tr></thead><tbody>
  <?php foreach ($S as $i => $r):
      $st = $now < $r['s'] ? 'soon' : ($now <= $r['e'] ? 'live' : 'done');
      $q = ['exam' => $r['exam']['slug']]; if (!empty($r['allday'])) $q['p'] = $r['passage']['id'];
      $url = add_query_arg($q, home_url('/live-typing/')); ?>
    <tr data-f="<?php echo esc_attr($r['lang']); ?>">
      <td><?php echo $i + 1; ?></td>
      <td><b><?php echo esc_html($r['exam']['name']); ?></b><?php if (!empty($r['no'])) echo '<br><span class="muted small">Passage ' . (int) $r['no'] . '</span>'; ?></td>
      <td class="small"><?php echo esc_html($r['passage']['title'] ?? '—'); ?><br><span class="muted"><?php echo $r['passage'] ? number_format_i18n($r['passage']['chars']) . ' key strokes' : ''; ?></span></td>
      <td><?php echo $r['lang'] === 'hi' ? 'Hindi<br><span class="muted small">Krutidev / Mangal Inscript</span>' : 'English'; ?></td>
      <td><?php echo !empty($r['allday']) ? 'All day' : esc_html(gmdate('h:i A', $r['s'] * 60) . ' – ' . gmdate('h:i A', $r['e'] * 60)); ?></td>
      <td><?php echo $st === 'live' ? '<span class="pill ok">● Open</span>' : ($st === 'soon' ? '<span class="pill">Soon</span>' : '<span class="pill no">Closed</span>'); ?></td>
      <td class="eh-live-act"><?php if ($st === 'live' && $r['lang'] === 'hi'): ?>
          <a class="btn dark sm" href="<?php echo esc_url(add_query_arg('kb', 'krutidev', $url)); ?>">Krutidev →</a>
          <a class="btn dark sm" href="<?php echo esc_url(add_query_arg('kb', 'inscript', $url)); ?>">Mangal →</a>
        <?php else: ?>
          <a class="btn <?php echo $st === 'live' ? 'dark' : 'ghost'; ?> sm" href="<?php echo esc_url($url); ?>"><?php echo $st === 'live' ? 'Start →' : ($st === 'done' ? 'Result' : 'View'); ?></a>
        <?php endif; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
<script>
(function(){var bs=document.querySelectorAll('.eh-live-chips button'),rows=document.querySelectorAll('.eh-live-table tbody tr');
bs.forEach(function(b){b.addEventListener('click',function(){bs.forEach(function(x){x.classList.toggle('on',x===b)});var f=b.getAttribute('data-f');rows.forEach(function(r){r.style.display=!f||r.getAttribute('data-f')===f?'':'none'})})});})();
</script>
<?php endif; get_footer();
