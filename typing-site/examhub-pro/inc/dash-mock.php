<?php
/* Student dashboard: one place for mock tests and typing — summary, trend, section strength, attempts, retest. */
if (!defined('ABSPATH')) exit;

function eh_dash_mock_data($uid) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . eh_stm_table() . ' WHERE uid = %d AND submitted > 0 ORDER BY submitted ASC', $uid), ARRAY_A);
    $out = []; $sec = [];
    foreach ($rows as $x) {
        $r = eh_stm_round($x['round']); if (!$r || $r['id'] !== $x['round']) continue;
        $nq = function_exists('eh_stm_nq') ? eh_stm_nq($r) : 120; $max = $nq * 3;
        $Q = eh_stm_paper($r); $a = json_decode($x['ans'] ?: '{}', true) ?: [];
        foreach (eh_stm_secmap($r) as $sm) {
            $k = $sm[0]; $sec[$k] = $sec[$k] ?? ['c' => 0, 'w' => 0, 'q' => 0];
            foreach ($sm[1] as $i) { $sec[$k]['q']++; if (!isset($a[$i])) continue; if (isset($Q[$i]) && $a[$i] === $Q[$i]['a']) $sec[$k]['c']++; else $sec[$k]['w']++; }
        }
        $rank = $x['counted'] ? 1 + (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_stm_table() . ' WHERE round = %s AND submitted > 0 AND counted = 1 AND score > %d', $x['round'], $x['score'])) : 0;
        $tot = $x['counted'] ? (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_stm_table() . ' WHERE round = %s AND submitted > 0 AND counted = 1', $x['round'])) : 0;
        $out[] = ['r' => $r, 'att' => (int) $x['att'], 'when' => (int) $x['submitted'], 'score' => (int) $x['score'], 'max' => $max, 'pct' => round(max(0, (int) $x['score']) * 100 / $max, 1),
            'c' => (int) $x['correct'], 'w' => (int) $x['wrong'], 'nq' => $nq, 'rank' => $rank, 'tot' => $tot, 'practice' => !$x['counted']];
    }
    return [$out, $sec];
}
function eh_dash_spark($vals, $max = 100, $color = '#e8590c') {
    $n = count($vals); if ($n < 2) return '';
    $w = 600; $h = 150; $pts = [];
    foreach (array_values($vals) as $i => $v) $pts[] = round($i * $w / ($n - 1), 1) . ',' . round($h - 10 - max(0, min($max, $v)) * ($h - 20) / $max, 1);
    $p = implode(' ', $pts);
    return '<svg class="dk-chart" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none"><defs><linearGradient id="g' . substr(md5($color), 0, 5) . '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $color . '" stop-opacity=".25"/><stop offset="1" stop-color="' . $color . '" stop-opacity="0"/></linearGradient></defs>'
        . '<polygon points="0,' . $h . ' ' . $p . ' ' . $w . ',' . $h . '" fill="url(#g' . substr(md5($color), 0, 5) . ')"/><polyline points="' . $p . '" fill="none" stroke="' . $color . '" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/></svg>';
}
function eh_dash_render($uid) {
    [$M, $S] = eh_dash_mock_data($uid);
    global $wpdb; $t = eh_table();
    $ty = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE user_id = %d ORDER BY id DESC LIMIT 20", $uid), ARRAY_A);
    $tyAgg = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) n, MAX(net) b, AVG(acc) a FROM $t WHERE user_id = %d", $uid), ARRAY_A);
    $u = wp_get_current_user(); $n = count($M);
    $avg = $n ? round(array_sum(array_column($M, 'pct')) / $n, 1) : 0; $best = $n ? max(array_column($M, 'pct')) : 0;
    $c = array_sum(array_column($M, 'c')); $w = array_sum(array_column($M, 'w')); $acc = $c + $w ? round($c * 100 / ($c + $w)) : 0;
    $br = array_filter(array_column($M, 'rank')); $bestRank = $br ? min($br) : 0;
    $hub = home_url('/mock-test/');
    ob_start(); ?>
<div class="dk">
 <div class="dk-hi"><div><span class="dk-k">मेरा डैशबोर्ड</span><h1>नमस्ते, <em><?php echo esc_html($u->display_name ?: $u->user_login); ?></em></h1><p>आपके सभी मॉक टेस्ट और टाइपिंग टेस्ट की प्रगति एक जगह।</p></div>
  <div class="dk-subs"><?php foreach (['tgt' => 'TGT', 'pgt' => 'PGT'] as $sr => $lb): $pf = eh_tp_pref($uid, $sr); ?>
   <a data-tp="<?php echo $sr; ?>" href="<?php echo esc_url($pf ? add_query_arg(['x' => $sr, 's' => $pf], $hub) : add_query_arg(['x' => $sr, 'all' => 1], $hub)); ?>"><span><?php echo $lb; ?> विषय</span><b><?php echo $pf ? esc_html(eh_tp_subjects()[$sr][$pf][0]) : 'अभी नहीं चुना'; ?></b><em><?php echo $pf ? 'टेस्ट देखें →' : 'चुनें →'; ?></em></a>
  <?php endforeach; ?></div></div>
 <nav class="dk-tabs" id="dk-tabs"><button class="on" data-t="m">📝 मॉक टेस्ट <i><?php echo $n; ?></i></button><button data-t="t">⌨️ टाइपिंग <i><?php echo (int) $tyAgg['n']; ?></i></button></nav>

 <div class="dk-p on" data-p="m">
 <?php if (!$n): ?><div class="dk-empty"><b>अभी तक कोई मॉक टेस्ट नहीं दिया</b><p>Super TET, TGT या PGT का फ्री लाइव मॉक दें — यहाँ आपकी पूरी प्रगति दिखेगी।</p><a class="dk-btn" href="<?php echo esc_url($hub); ?>">मॉक टेस्ट दें →</a></div>
 <?php else: ?>
  <div class="dk-kpi"><div><b><?php echo $n; ?></b><span>टेस्ट दिए</span></div><div><b><?php echo $avg; ?>%</b><span>औसत अंक</span></div><div><b><?php echo $best; ?>%</b><span>सर्वश्रेष्ठ</span></div><div><b><?php echo $acc; ?>%</b><span>सटीकता</span></div><div><b><?php echo $bestRank ? '#' . $bestRank : '—'; ?></b><span>सर्वश्रेष्ठ रैंक</span></div></div>
  <div class="dk-grid">
   <div class="dk-card"><h3>अंकों का ग्राफ़ <small>(% में, हर प्रयास)</small></h3><?php echo $n > 1 ? eh_dash_spark(array_column($M, 'pct')) : '<p class="dk-note">ग्राफ़ के लिए कम से कम 2 टेस्ट दें।</p>'; ?></div>
   <div class="dk-card"><h3>विषय / सेक्शन की पकड़</h3>
   <?php $min = null; foreach ($S as $k => $v) { $at = $v['c'] + $v['w']; $p = $at ? round($v['c'] * 100 / $at) : 0; if ($at && ($min === null || $p < $min[1])) $min = [$k, $p]; } ?>
   <?php foreach ($S as $k => $v): $at = $v['c'] + $v['w']; if (!$at) continue; $p = round($v['c'] * 100 / $at); ?>
    <div class="dk-sec<?php echo $min && $min[0] === $k ? ' weak' : ''; ?>"><div><span><?php echo esc_html($k); ?></span><b><?php echo $p; ?>%</b></div><div class="dk-bar"><i style="width:<?php echo $p; ?>%"></i></div><small>सही <?php echo $v['c']; ?> · गलत <?php echo $v['w']; ?> · छोड़े <?php echo $v['q'] - $at; ?></small></div>
   <?php endforeach; ?>
   <?php if ($min && count($S) > 1): ?><p class="dk-tip">💡 <b><?php echo esc_html($min[0]); ?></b> में सटीकता सबसे कम है — इस पर अधिक अभ्यास करें।</p><?php endif; ?></div>
  </div>
  <div class="dk-card"><h3>मेरे सभी प्रयास</h3><div class="dk-list">
  <?php foreach (array_reverse($M) as $x): $r = $x['r']; $url = function_exists('eh_tp_is') && eh_tp_is($r) ? eh_tp_url($r) : add_query_arg($r['id'] === eh_stm_round()['id'] ? [] : ['r' => $r['id']], home_url('/super-tet-mock/')); ?>
   <div class="dk-row"><div class="dk-rl"><b><?php echo esc_html($r['name']); ?></b><small><?php echo esc_html(wp_date('d M Y, h:i A', $x['when'])); ?> · Attempt <?php echo $x['att']; ?><?php echo $x['practice'] ? ' · अभ्यास' : ''; ?></small>
    <div class="dk-bar s"><i style="width:<?php echo $x['pct']; ?>%"></i></div></div>
    <div class="dk-rs"><b><?php echo $x['score']; ?><small>/<?php echo $x['max']; ?></small></b><span>सही <?php echo $x['c']; ?> · गलत <?php echo $x['w']; ?><?php echo $x['rank'] ? ' · Rank ' . $x['rank'] . '/' . $x['tot'] : ''; ?></span></div>
    <div class="dk-ra"><a class="dk-btn ghost" href="<?php echo esc_url($url); ?>">View Results</a><?php if (eh_stm_status($r) === 'live'): ?><a class="dk-btn" href="<?php echo esc_url(add_query_arg('re', 1, $url)); ?>">↻ Reattempt</a><?php endif; ?></div></div>
  <?php endforeach; ?></div></div>
 <?php endif; ?>
 </div>

 <div class="dk-p" data-p="t">
  <div class="dk-kpi"><div><b><?php echo (int) $tyAgg['n']; ?></b><span>टाइपिंग टेस्ट</span></div><div><b><?php echo (int) $tyAgg['b']; ?> <small>wpm</small></b><span>सर्वश्रेष्ठ नेट स्पीड</span></div><div><b><?php echo (int) round((float) $tyAgg['a']); ?>%</b><span>औसत सटीकता</span></div></div>
  <?php if (count($ty) > 1): ?><div class="dk-card"><h3>नेट स्पीड का ग्राफ़ <small>(हाल के 20 टेस्ट, wpm)</small></h3><?php $sp = array_reverse(array_map(function ($r) { return (float) $r['net']; }, $ty)); echo eh_dash_spark($sp, max(40, max($sp) * 1.15), '#1d63c7'); ?></div><?php endif; ?>
 </div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var B=[].slice.call(document.querySelectorAll('#dk-tabs button')),P=[].slice.call(document.querySelectorAll('.dk-p')),H=document.getElementById('dk-typing-full');
function go(t){B.forEach(function(x){x.classList.toggle('on',x.dataset.t===t)});P.forEach(function(p){p.classList.toggle('on',p.dataset.p===t)});if(H)H.style.display=t==='t'?'':'none';try{localStorage.setItem('eh_dk_tab',t)}catch(e){}}
B.forEach(function(b){b.onclick=function(){go(b.dataset.t)}});var s=/[?&](f|pg)=/.test(location.search)?'t':null;try{s=s||localStorage.getItem('eh_dk_tab')}catch(e){}go(s||'m')});</script>
<?php return ob_get_clean() . (function_exists('eh_tp_picker') ? eh_tp_picker() : '');
}
