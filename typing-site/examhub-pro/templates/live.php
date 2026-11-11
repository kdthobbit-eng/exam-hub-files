<?php
$eh_days = eh_live_days();
$eh_day = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['day'] ?? '')) && in_array($_GET['day'], $eh_days, true) ? $_GET['day'] : '';
$S = eh_live_schedule($eh_day);
$now = (int) wp_date('G') * 60 + (int) wp_date('i');
$sel = sanitize_title($_GET['exam'] ?? '');
$pid = sanitize_key($_GET['p'] ?? ''); $kb = sanitize_key($_GET['kb'] ?? '');
$row = null; foreach ($S as $r) if (!$row && $r['exam']['slug'] === $sel && ($pid === '' || ($r['passage']['id'] ?? '') === $pid)) $row = $r;
/* a passage of an older day still opens from its link */
if (!$row && $pid !== '') foreach ($eh_days as $d) { foreach (eh_live_schedule($d) as $r) if (!$row && $r['exam']['slug'] === $sel && ($r['passage']['id'] ?? '') === $pid) $row = $r; if ($row) break; }
$medal = ['🥇', '🥈', '🥉'];
global $wpdb;
/* passages this user has already typed in Live Typing: [passage id => best row] */
$eh_uid = get_current_user_id(); $eh_done = [];
if ($eh_uid) foreach ((array) $wpdb->get_results($wpdb->prepare('SELECT id, rkey, passage, exam, net, pass FROM ' . eh_table() . " WHERE user_id = %d AND live_date IS NOT NULL ORDER BY net ASC", $eh_uid), ARRAY_A) as $d) $eh_done[$d['exam'] . '|' . $d['passage']] = $d;
$eh_back = home_url(add_query_arg([]));
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
    <h1><?php echo esc_html($L['exam']['name'] . (!empty($L['no']) ? ' · Passage ' . (int) $L['no'] . (!empty($L['level']) ? ' · ' . $L['level'] : '') : '')); ?></h1>
    <p class="lead"><?php echo $allday ? 'This passage is the same for everyone today and is open all day. Ranking is by net speed.' : 'Today’s passage is the same for everyone. Open from ' . esc_html($L['start'] . ' to ' . $L['end']) . '. Take the test in this window; ranking is by net speed.'; ?></p>
  </div>
  <div class="eh-live-cards">
    <div class="eh-lc"><span>Today’s passage</span><b><?php echo esc_html($L['passage']['title'] ?? '—'); ?></b><em><?php echo $L['lang'] === 'hi' ? 'Hindi' : 'English'; ?> · <?php echo !empty($L['level']) ? esc_html($L['level']) . ' · ' . number_format_i18n($L['chars']) . ' key strokes' : esc_html(eh_target_label($L['exam'])); ?></em></div>
    <div class="eh-lc"><span>Tests today</span><b><?php echo number_format_i18n($players); ?></b><em>people</em></div>
    <div class="eh-lc dark"><span><?php echo $open ? ($allday ? 'Time left today' : 'Closes in') : ($now < $L['s'] ? 'Opens in' : 'Back tomorrow'); ?></span><b id="eh-cd" data-s="<?php echo $open ? ($L['e'] - $now) * 60 : ($now < $L['s'] ? ($L['s'] - $now) * 60 : 0); ?>">--:--:--</b><em><?php echo $allday ? 'Open all day' : esc_html($L['start'] . ' – ' . $L['end']); ?></em></div>
  </div>
</div></section>
<section class="wrap eh-section eh-live">
  <div><?php $eh_mine = $eh_done[$L['exam']['slug'] . '|' . $lp] ?? null;
    if ($open && $L['passage'] && !$eh_uid): ?>
      <div class="eh-panel eh-live-lock"><span class="eh-live-lock-ic">🔒</span>
        <h2>Log in to take Live Typing</h2>
        <p class="muted">Live Typing has a ranking, so each test is saved to your account. It is free and takes one minute.</p>
        <div class="eh-live-lock-btns"><a class="btn dark" href="<?php echo esc_url(eh_login_page_url($eh_back)); ?>">Log in →</a>
        <a class="btn ghost" href="<?php echo esc_url(add_query_arg('redirect_to', rawurlencode($eh_back), eh_signup_page_url())); ?>">New user? Sign up</a></div>
      </div>
    <?php elseif ($open && $L['passage'] && $eh_mine): ?>
      <div class="eh-panel eh-live-lock done"><span class="eh-live-lock-ic">✅</span>
        <h2>Attempted</h2>
        <p class="muted">You have already typed this passage. Your speed: <b><?php echo (int) $eh_mine['net']; ?> WPM</b> · <?php echo $eh_mine['pass'] ? 'Qualified' : 'Not qualified'; ?>. Each live passage can be typed once, so the ranking stays fair.</p>
        <div class="eh-live-lock-btns"><a class="btn dark" href="<?php echo esc_url(eh_result_url($eh_mine['id'], $eh_mine['rkey'])); ?>">See my result →</a>
        <a class="btn ghost" href="<?php echo esc_url(home_url('/live-typing/')); ?>">Other passages</a></div>
      </div>
    <?php elseif ($open && $L['passage']):
      $eh_exam = $L['exam']; if ($eh_exam['lang'] !== 'hi') $eh_exam['lang'] = 'en';
      $eh_cfg = ['fixedPassage' => $L['passage']['id'], 'lockTime' => true, 'live' => $L['date']];
      if (!empty($L['plist'])) $eh_cfg['passages'] = $L['plist'];
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
  <?php $eh_news = ''; foreach ($S as $x) if (!empty($x['news'])) $eh_news = $x['news'];
  $eh_fd = function ($d) { return date_i18n('j M Y', strtotime($d)); };
  $eh_cur = $eh_news ?: wp_date('Y-m-d'); $eh_ix = array_search($eh_cur, $eh_days, true);
  $eh_u = function ($d) use ($eh_days) { return $d === ($eh_days[0] ?? '') ? home_url('/live-typing/') : add_query_arg('day', $d, home_url('/live-typing/')); };
  $eh_newer = $eh_ix !== false && $eh_ix > 0 ? $eh_days[$eh_ix - 1] : ''; $eh_older = $eh_ix !== false && isset($eh_days[$eh_ix + 1]) ? $eh_days[$eh_ix + 1] : '';
  if ($eh_days): ?>
  <div class="eh-dp" id="eh-dp" data-days="<?php echo esc_attr(implode(',', $eh_days)); ?>" data-cur="<?php echo esc_attr($eh_cur); ?>" data-base="<?php echo esc_url(home_url('/live-typing/')); ?>">
    <a class="eh-dp-nav<?php echo $eh_older ? '' : ' off'; ?>" <?php echo $eh_older ? 'href="' . esc_url($eh_u($eh_older)) . '"' : ''; ?> aria-label="Previous day">‹</a>
    <button type="button" class="eh-dp-btn" id="eh-dp-btn" aria-haspopup="true">
      <span class="eh-dp-ic">📅</span>
      <span class="eh-dp-t"><b><?php echo esc_html(date_i18n('j M Y', strtotime($eh_cur))); ?></b><small><?php echo $eh_cur === wp_date('Y-m-d') ? 'Today' : esc_html(date_i18n('l', strtotime($eh_cur))); ?></small></span>
      <span class="eh-dp-ar">▾</span>
    </button>
    <a class="eh-dp-nav<?php echo $eh_newer ? '' : ' off'; ?>" <?php echo $eh_newer ? 'href="' . esc_url($eh_u($eh_newer)) . '"' : ''; ?> aria-label="Next day">›</a>
    <?php if ($eh_ix !== 0): ?><a class="eh-dp-today" href="<?php echo esc_url(home_url('/live-typing/')); ?>">Today</a><?php endif; ?>
    <div class="eh-dp-pop" id="eh-dp-pop" hidden></div>
  </div>
  <?php endif; ?>
  <p class="eh-dp-sum"><b><?php echo count($S); ?></b> passages<?php if ($eh_news): ?> · <span class="eh-lvl easy">10 Easy</span> <span class="eh-lvl moderate">10 Moderate</span> <span class="eh-lvl hard">10 Hard</span> · English + Hindi · from the day's news<?php endif; ?></p>
  <script>
  (function () {
    var box = document.getElementById('eh-dp'); if (!box) return;
    var days = box.getAttribute('data-days').split(','), cur = box.getAttribute('data-cur'), base = box.getAttribute('data-base');
    var pop = document.getElementById('eh-dp-pop'), btn = document.getElementById('eh-dp-btn');
    var M = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var y = +cur.slice(0, 4), m = +cur.slice(5, 7) - 1;
    function z(n) { return (n < 10 ? '0' : '') + n; }
    function draw() {
      var first = new Date(y, m, 1).getDay(), n = new Date(y, m + 1, 0).getDate(), h = '<div class="eh-dp-h"><button type="button" data-s="-1">‹</button><b>' + M[m] + ' ' + y + '</b><button type="button" data-s="1">›</button></div><div class="eh-dp-g">';
      ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(function (d) { h += '<i>' + d + '</i>'; });
      for (var k = 0; k < first; k++) h += '<span></span>';
      for (var d = 1; d <= n; d++) {
        var id = y + '-' + z(m + 1) + '-' + z(d);
        h += days.indexOf(id) > -1 ? '<a class="' + (id === cur ? 'on' : '') + '" href="' + (id === days[0] ? base : base + '?day=' + id) + '">' + d + '</a>' : '<span class="na">' + d + '</span>';
      }
      pop.innerHTML = h + '</div><p>Dates in colour have passages.</p>';
    }
    btn.addEventListener('click', function (e) { e.stopPropagation(); if (pop.hidden) draw(); pop.hidden = !pop.hidden; });
    pop.addEventListener('click', function (e) { e.stopPropagation(); var s = e.target.getAttribute && e.target.getAttribute('data-s'); if (s) { m += +s; if (m < 0) { m = 11; y--; } if (m > 11) { m = 0; y++; } draw(); } });
    document.addEventListener('click', function () { pop.hidden = true; });
  })();
  </script>
  <div class="eh-chips eh-live-chips"><button type="button" class="on" data-f="">All</button><button type="button" data-f="en">English</button><button type="button" data-f="hi">Hindi</button></div>
  <?php else: ?>
  <h1 class="eh-live-h">Live Typing — same passage, same time, for everyone</h1>
  <p class="muted">Each exam has a live test at a fixed time every day. Everyone gets the same passage and the day's ranking is by net speed.</p>
  <div class="eh-chips eh-live-chips"><button type="button" class="on" data-f="">All</button><button type="button" data-f="en">English</button><button type="button" data-f="hi">Hindi</button></div>
  <?php endif; ?>
  <div class="eh-table-wrap"><table class="eh-table eh-live-table"><thead><tr><th>#</th><th>Test</th><th>Level</th><th>Passage</th><th>Language</th><th>Time</th><th>Status</th><th>Start</th></tr></thead><tbody>
  <?php foreach ($S as $i => $r):
      $st = $now < $r['s'] ? 'soon' : ($now <= $r['e'] ? 'live' : 'done');
      $q = ['exam' => $r['exam']['slug']]; if (!empty($r['allday'])) $q['p'] = $r['passage']['id'];
      $url = add_query_arg($q, home_url('/live-typing/')); ?>
    <tr data-f="<?php echo esc_attr($r['lang']); ?>" class="<?php echo isset($eh_done[$r['exam']['slug'] . '|' . ($r['passage']['id'] ?? '')]) ? 'eh-row-att' : ''; ?>">
      <td><?php echo $i + 1; ?></td>
      <td><b><?php echo esc_html($r['exam']['name']); ?></b><?php if (!empty($r['no'])) echo '<br><span class="muted small">Passage ' . (int) $r['no'] . '</span>'; ?></td>
      <td><?php echo !empty($r['level']) ? '<span class="eh-lvl ' . esc_attr(strtolower($r['level'])) . '">' . esc_html($r['level']) . '</span>' : '—'; ?></td>
      <td class="small"><?php echo esc_html($r['passage']['title'] ?? '—'); ?><br><span class="muted"><?php echo $r['passage'] ? number_format_i18n($r['chars'] ?? $r['passage']['chars']) . ' key strokes' : ''; ?></span></td>
      <td><?php echo $r['lang'] === 'hi' ? 'Hindi<br><span class="muted small">Krutidev / Mangal Inscript</span>' : 'English'; ?></td>
      <td><?php echo !empty($r['allday']) ? 'All day' : esc_html(gmdate('h:i A', $r['s'] * 60) . ' – ' . gmdate('h:i A', $r['e'] * 60)); ?></td>
      <?php $mine = $eh_done[$r['exam']['slug'] . '|' . ($r['passage']['id'] ?? '')] ?? null; ?>
      <td><?php echo $mine ? '<span class="pill eh-att">✓ Attempted</span>' : ($st === 'live' ? '<span class="pill ok">● Open</span>' : ($st === 'soon' ? '<span class="pill">Soon</span>' : '<span class="pill no">Closed</span>')); ?></td>
      <td class="eh-live-act"><?php if ($mine): ?>
          <a class="btn ghost sm" href="<?php echo esc_url(eh_result_url($mine['id'], $mine['rkey'])); ?>"><?php echo (int) $mine['net']; ?> WPM · Result</a>
        <?php elseif ($st === 'live' && !$eh_uid): ?>
          <a class="btn dark sm" href="<?php echo esc_url(eh_login_page_url($url)); ?>">🔒 Log in to start</a>
        <?php elseif ($st === 'live' && $r['lang'] === 'hi'): ?>
          <a class="btn dark sm" href="<?php echo esc_url(add_query_arg('kb', 'krutidev', $url)); ?>">Krutidev →</a>
          <a class="btn dark sm" href="<?php echo esc_url(add_query_arg('kb', 'inscript', $url)); ?>">Mangal →</a>
        <?php else: ?>
          <a class="btn <?php echo $st === 'live' ? 'dark' : 'ghost'; ?> sm" href="<?php echo esc_url($url); ?>"><?php echo $st === 'live' ? 'Start →' : ($st === 'done' ? 'Result' : 'View'); ?></a>
        <?php endif; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <div class="eh-live-pg" id="eh-live-pg"></div>
</section>
<script>
(function(){var bs=document.querySelectorAll('.eh-live-chips button'),rows=[].slice.call(document.querySelectorAll('.eh-live-table tbody tr')),pg=document.getElementById('eh-live-pg'),per=20,f='',page=1;
function draw(){var list=rows.filter(function(r){return !f||r.getAttribute('data-f')===f;}),n=Math.max(1,Math.ceil(list.length/per));if(page>n)page=n;
rows.forEach(function(r){r.style.display='none';});list.forEach(function(r,i){if(i>=(page-1)*per&&i<page*per)r.style.display='';});
if(!pg)return;if(n<2){pg.innerHTML='';return;}var h='<button type="button" data-p="'+(page-1)+'"'+(page<2?' disabled':'')+'>← Prev</button>';
for(var k=1;k<=n;k++)h+='<button type="button" data-p="'+k+'" class="'+(k===page?'on':'')+'">'+k+'</button>';
pg.innerHTML=h+'<button type="button" data-p="'+(page+1)+'"'+(page>=n?' disabled':'')+'>Next →</button><span>Page '+page+' of '+n+'</span>';}
if(pg)pg.addEventListener('click',function(e){var p=e.target.getAttribute&&e.target.getAttribute('data-p');if(!p||e.target.disabled)return;page=+p;draw();var t=document.querySelector('.eh-live-chips');if(t)t.scrollIntoView({behavior:'smooth',block:'start'});});
bs.forEach(function(b){b.addEventListener('click',function(){bs.forEach(function(x){x.classList.toggle('on',x===b)});f=b.getAttribute('data-f');page=1;draw();});});draw();})();
</script>
<?php endif; get_footer();
