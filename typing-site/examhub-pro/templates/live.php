<?php get_header();
$L = eh_live_info();
$eh_exam = ['slug' => 'demo', 'name' => 'Daily Live Typing — ' . wp_date('d M Y'), 'lang' => $L['lang'], 'unit' => 'words', 'target' => $L['lang'] === 'hi' ? 250 : 300, 'minutes' => $L['minutes'], 'cat' => 'other', 'allowed' => (float) eh_opt('allowed_error'), 'retype' => false];
$eh_cfg = ['fixedPassage' => $L['passage'], 'lockTime' => true, 'live' => $L['date']];
$board = eh_leaderboard('', $L['date'], 25);
$yday = eh_leaderboard('', wp_date('Y-m-d', strtotime('-1 day', current_time('timestamp'))), 3);
global $wpdb; $players = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_table() . ' WHERE live_date = %s', $L['date']));
$title = ''; foreach (eh_passages($L['lang']) as $p) if ($p['id'] === $L['passage']) $title = $p['title'];
$midnight = strtotime('tomorrow', current_time('timestamp')) - current_time('timestamp');
$medal = ['🥇', '🥈', '🥉'];
?>
<section class="eh-live-hero"><div class="wrap eh-live-hero-in">
  <div>
    <span class="eh-live-dot">LIVE</span>
    <h1>Daily Live Typing</h1>
    <p class="lead">आज का पैसेज सबके लिए एक ही है। <?php echo (int) $L['minutes']; ?> मिनट का टेस्ट दीजिए और देखिए आज की ranking में आप कहाँ हैं।</p>
  </div>
  <div class="eh-live-cards">
    <div class="eh-lc"><span>आज का पैसेज</span><b><?php echo esc_html($title ?: '—'); ?></b><em><?php echo $L['lang'] === 'hi' ? 'Hindi · Krutidev / Mangal' : 'English'; ?></em></div>
    <div class="eh-lc"><span>आज टेस्ट दिया</span><b><?php echo number_format_i18n($players); ?></b><em>लोगों ने</em></div>
    <div class="eh-lc dark"><span>नया पैसेज आने में</span><b id="eh-cd" data-s="<?php echo (int) $midnight; ?>">--:--:--</b><em>रात 12 बजे</em></div>
  </div>
</div></section>
<section class="wrap eh-section eh-live">
  <div><?php include __DIR__ . '/_test-ui.php'; ?></div>
  <aside>
    <div class="eh-panel eh-rank">
      <div class="eh-rank-head"><h2>आज की Ranking</h2><span class="muted small" id="eh-rk-time">अपने-आप अपडेट</span></div>
      <div class="eh-podium" id="eh-podium">
        <?php for ($i = 0; $i < 3; $i++): $r = $board[$i] ?? null; ?>
          <div class="p p<?php echo $i + 1; ?>"><i><?php echo $medal[$i]; ?></i><b><?php echo $r ? esc_html(eh_display_name($r)) : '—'; ?></b><span><?php echo $r ? (int) $r['net'] . ' WPM' : 'खाली'; ?></span></div>
        <?php endfor; ?>
      </div>
      <ol class="eh-rk-list" id="eh-rk-list" start="4">
        <?php foreach (array_slice($board, 3) as $r): ?><li><span><?php echo esc_html(eh_display_name($r)); ?></span><b><?php echo (int) $r['net']; ?></b><em><?php echo esc_html($r['acc']); ?>%</em></li><?php endforeach; ?>
      </ol>
      <?php if (!$board): ?><p class="muted small" id="eh-rk-empty">आज अभी किसी ने टेस्ट नहीं दिया — पहली रैंक आपकी हो सकती है!</p><?php endif; ?>
    </div>
    <?php if ($yday): ?>
    <div class="eh-panel eh-yday"><h3>कल के Toppers</h3>
      <?php foreach ($yday as $i => $r): ?><div><i><?php echo $medal[$i]; ?></i><span><?php echo esc_html(eh_display_name($r)); ?></span><b><?php echo (int) $r['net']; ?> WPM</b></div><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </aside>
</section>
<script>
(function(){
  var cd=document.getElementById('eh-cd'),s=+cd.dataset.s;
  function t(){if(s<=0){location.reload();return}var h=Math.floor(s/3600),m=Math.floor(s%3600/60),x=s%60;cd.textContent=[h,m,x].map(function(v){return(v<10?'0':'')+v}).join(':');s--}
  t();setInterval(t,1000);
  var api='<?php echo esc_url_raw(rest_url('eh/v1/leaderboard')); ?>?date=<?php echo esc_js($L['date']); ?>',md=['🥇','🥈','🥉'];
  function esc(v){return String(v).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
  function load(){fetch(api,{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(rows){
    if(!Array.isArray(rows))return;
    var me='';try{me=localStorage.getItem('eh_name')||''}catch(e){}
    document.getElementById('eh-podium').innerHTML=[0,1,2].map(function(i){var r=rows[i];return '<div class="p p'+(i+1)+'"><i>'+md[i]+'</i><b>'+(r?esc(r.name):'—')+'</b><span>'+(r?r.net+' WPM':'खाली')+'</span></div>'}).join('');
    document.getElementById('eh-rk-list').innerHTML=rows.slice(3,25).map(function(r){return '<li'+(me&&r.name===me?' class="me"':'')+'><span>'+esc(r.name)+'</span><b>'+r.net+'</b><em>'+r.acc+'%</em></li>'}).join('');
    var e=document.getElementById('eh-rk-empty');if(e&&rows.length)e.remove();
    document.getElementById('eh-rk-time').textContent='अपडेट: '+new Date().toLocaleTimeString('hi-IN',{hour:'2-digit',minute:'2-digit'});
  }).catch(function(){})}
  setInterval(load,30000);
  document.addEventListener('eh:saved',load);
})();
</script>
<?php get_footer();
