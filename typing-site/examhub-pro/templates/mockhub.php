<?php
/* Live Mock hub: Super TET, UP TGT, UP PGT. TGT/PGT: the student picks a subject once (saved on the server, changeable any time),
   then lands in that subject's test zone: full mock, sectional mocks, topic-wise sets and previous-year papers. */
if (!defined('ABSPATH')) exit;
nocache_headers();
$x = sanitize_key($_GET['x'] ?? '');
if (!in_array($x, ['tgt', 'pgt'], true)) $x = '';
$sub = $x ? sanitize_key($_GET['s'] ?? '') : '';
if ($sub && !isset(eh_tp_subjects()[$x][$sub])) $sub = '';
$uid = get_current_user_id();
if ($x && !$sub && empty($_GET['all']) && ($p = eh_tp_pref($uid, $x))) { wp_safe_redirect(add_query_arg(['x' => $x, 's' => $p], home_url('/mock-test/'))); exit; }
if ($x && $sub && $uid) update_user_meta($uid, 'eh_tp_pref_' . $x, $sub);
$info = $x ? eh_tp_info($x) : null;
$rounds = $x ? array_values(array_filter(eh_tp_rounds(), function ($r) use ($x) { return $r['series'] === $x; })) : [];
$hub = home_url('/mock-test/');
get_header();
$hub = home_url('/mock-test/');
?>
<main class="mx">
<?php if (!$x): ?>
 <section class="mx-hero"><div class="mx-wrap">
  <span class="mx-kick"><i></i>Live Mock Test · UPSSSC ALERT</span>
  <h1>परीक्षा जैसा <em>लाइव मॉक टेस्ट</em></h1>
  <p>असली परीक्षा जैसी कंप्यूटर आधारित स्क्रीन, सर्वर पर चलने वाली घड़ी, सबमिट करते ही स्कोर, रैंक, परसेंटाइल और हर प्रश्न की व्याख्या। अपनी परीक्षा चुनें।</p>
  <div class="mx-facts"><div><b>120</b><span>प्रश्न</span></div><div><b>360</b><span>अंक</span></div><div><b>2 घंटे</b><span>समय</span></div><div><b>₹0</b><span>बिल्कुल फ्री</span></div></div>
 </div></section>
 <section class="mx-body"><div class="mx-wrap"><?php echo eh_mock_cards(); ?></div></section>
<?php elseif (!$sub): $ready = count(array_filter($rounds, 'eh_tp_ready')); ?>
 <section class="mz-bar"><div class="mx-wrap">
  <div class="mz-crumb"><a href="<?php echo esc_url($hub); ?>">← Live Mock</a></div>
  <div class="mz-head"><div><span class="mx-kick"><i></i><?php echo esc_html($info['title'] . ' · ' . $info['adv']); ?></span><h1>अपना <em>विषय</em> चुनें</h1>
   <p>एक बार चुनें — आपका विषय सेव रहेगा और अगली बार सीधे आपके विषय के टेस्ट खुलेंगे। कभी भी बदल सकते हैं।</p></div>
   <label class="mx-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input type="search" id="mx-q" placeholder="विषय खोजें — जैसे गणित, English"></label></div>
 </div></section>
 <section class="mx-body"><div class="mx-wrap">
  <div class="mx-subs-wrap"><div class="mx-tabs" id="mx-tabs"><button class="on" data-f="all">सभी विषय (<?php echo count($rounds); ?>)</button><button data-f="on">लाइव (<?php echo $ready; ?>)</button><button data-f="soon">जल्द आने वाले (<?php echo count($rounds) - $ready; ?>)</button></div>
  <div class="mx-subs" id="mx-subs"><?php $i = 0; $pf = eh_tp_pref($uid, $x); foreach ($rounds as $r): $i++; $ok = eh_tp_ready($r); ?>
   <a class="mx-s<?php echo $ok ? '' : ' soon'; ?><?php echo $pf === $r['slug'] ? ' mine' : ''; ?>" data-sub="<?php echo esc_attr($r['slug']); ?>" href="<?php echo esc_url(add_query_arg(['x' => $x, 's' => $r['slug']], $hub)); ?>" data-k="<?php echo esc_attr(mb_strtolower($r['sub'] . ' ' . $r['sub_en'])); ?>"><span class="ix"><?php echo str_pad((string) $i, 2, '0', STR_PAD_LEFT); ?></span><span class="tx"><b><?php echo esc_html($r['sub']); ?></b><small><?php echo esc_html($r['sub_en']); ?></small></span><span class="rt"><span class="mx-st <?php echo $ok ? 'on' : 'soon'; ?>"><?php echo $pf === $r['slug'] ? 'आपका विषय' : ($ok ? 'LIVE' : 'जल्द'); ?></span></span><span class="go"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>
  <?php endforeach; ?></div>
  <div class="mx-empty" id="mx-empty">कोई विषय नहीं मिला।</div></div>
 </div></section>
 <script>(function(){var X=<?php echo wp_json_encode($x); ?>,ALL=/[?&]all=1/.test(location.search),IN=<?php echo $uid ? 'true' : 'false'; ?>;
 try{var p=localStorage.getItem('eh_tp_'+X);if(p&&!ALL&&!IN){location.replace(location.pathname+'?x='+X+'&s='+p);return}}catch(e){}
 [].forEach.call(document.querySelectorAll('#mx-subs .mx-s'),function(a){a.addEventListener('click',function(){try{localStorage.setItem('eh_tp_'+X,a.dataset.sub)}catch(e){}})});
 var q=document.getElementById('mx-q'),L=[].slice.call(document.querySelectorAll('#mx-subs .mx-s')),E=document.getElementById('mx-empty'),F='all',run=function(){var v=q.value.trim().toLowerCase(),n=0;L.forEach(function(a){var s=(!v||a.dataset.k.indexOf(v)>-1)&&(F==='all'||(F==='soon')===a.classList.contains('soon'));a.style.display=s?'':'none';if(s)n++});E.style.display=n?'none':'block'};q.addEventListener('input',run);
 [].forEach.call(document.querySelectorAll('#mx-tabs button'),function(b){b.onclick=function(){F=b.dataset.f;[].forEach.call(document.querySelectorAll('#mx-tabs button'),function(x){x.classList.toggle('on',x===b)});run()}})})();</script>
<?php else:
  $R = eh_tp_find(($x === 'tgt' ? 'tg' : 'pg') . str_pad((string) (array_search($sub, array_keys(eh_tp_subjects()[$x])) + 1), 2, '0', STR_PAD_LEFT));
  $RS = eh_tp_find($R['id'] . 's'); $GS = eh_tp_find('gsx'); $my = eh_tp_my($uid); $nm = eh_tp_subjects()[$x][$sub]; ?>
 <section class="mz-bar"><div class="mx-wrap">
  <div class="mz-crumb"><a href="<?php echo esc_url($hub); ?>">← Live Mock</a></div>
  <div class="mz-head"><div><span class="mx-kick"><i></i><?php echo esc_html($info['title'] . ' · ' . $info['adv']); ?></span><h1><?php echo esc_html($info['short']); ?> <em><?php echo esc_html($nm[0]); ?></em></h1>
   <p><?php echo esc_html($info['post']); ?> · <?php echo esc_html($nm[1]); ?></p></div>
   <a class="mz-chip" data-tp="<?php echo esc_attr($x); ?>" data-tp-change href="<?php echo esc_url(add_query_arg(['x' => $x, 'all' => 1], $hub)); ?>"><span>आपका विषय</span><b>📘 <?php echo esc_html($nm[0]); ?></b><em>बदलें ✎</em></a></div>
  <nav class="mz-tabs" id="mz-tabs"><button class="on" data-t="full">फुल मॉक</button><button data-t="sec">सेक्शनल मॉक</button><button data-t="topic">टॉपिक-वाइज</button><button data-t="pyq">पिछले वर्ष के पेपर</button></nav>
 </div></section>
 <section class="mx-body"><div class="mx-wrap">
  <div class="mz-p on" data-p="full"><div class="mt-g"><?php echo eh_tp_test_card($R, $my, $info['short'] . ' ' . $nm[0] . ' — फुल मॉक 1', 'असली परीक्षा जैसा पूरा प्रश्न-पत्र: 30 सामान्य अध्ययन + 90 ' . $nm[0], 'फुल मॉक'); ?></div></div>
  <div class="mz-p" data-p="sec"><div class="mt-g"><?php echo eh_tp_test_card($RS, $my, $nm[0] . ' — सेक्शनल मॉक', 'केवल ' . $nm[0] . ' के 90 प्रश्न', 'विषय सेक्शनल') . eh_tp_test_card($GS, $my, 'सामान्य अध्ययन — सेक्शनल मॉक', 'TGT व PGT दोनों का कॉमन GS भाग — 30 प्रश्न', 'GS सेक्शनल'); ?></div></div>
  <div class="mz-p" data-p="topic"><div class="mz-empty"><b>📚 टॉपिक-वाइज सेट जल्द आ रहे हैं</b><p><?php echo esc_html($nm[0]); ?> के हर टॉपिक पर 50-50 प्रश्नों के सेट, विस्तृत हल के साथ।</p></div></div>
  <div class="mz-p" data-p="pyq"><div class="mz-empty"><b>🗂️ पिछले वर्ष के प्रश्न-पत्र जल्द आ रहे हैं</b><p>UPESSC / UPSESSB के असली पिछले प्रश्न-पत्र, उत्तर-कुंजी और व्याख्या के साथ।</p></div></div>
 </div></section>
 <script>(function(){try{localStorage.setItem('eh_tp_'+<?php echo wp_json_encode($x); ?>,<?php echo wp_json_encode($sub); ?>)}catch(e){}
 var B=[].slice.call(document.querySelectorAll('#mz-tabs button')),P=[].slice.call(document.querySelectorAll('.mz-p'));B.forEach(function(b){b.onclick=function(){B.forEach(function(x){x.classList.toggle('on',x===b)});P.forEach(function(p){p.classList.toggle('on',p.dataset.p===b.dataset.t)})}})})();</script>
<?php endif; ?>
</main>
<?php echo eh_tp_picker(); if ($x && !$sub): ?><script>if(window.ehTpOpen)ehTpOpen(<?php echo wp_json_encode($x); ?>)</script><?php endif; ?>
<?php get_footer();
