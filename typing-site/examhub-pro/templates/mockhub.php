<?php
/* Live Mock hub: Super TET, UP TGT, UP PGT. TGT/PGT open a subject list; a subject opens its live mock. */
if (!defined('ABSPATH')) exit;
nocache_headers();
$x = sanitize_key($_GET['x'] ?? '');
if (!in_array($x, ['tgt', 'pgt'], true)) $x = '';
$info = $x ? eh_tp_info($x) : null;
$rounds = $x ? array_values(array_filter(eh_tp_rounds(), function ($r) use ($x) { return $r['series'] === $x; })) : [];
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
<?php else: $ready = count(array_filter($rounds, 'eh_tp_ready')); ?>
 <section class="mx-hero"><div class="mx-wrap">
  <div class="mx-crumb"><a href="<?php echo esc_url($hub); ?>">Live Mock</a><span>/</span><?php echo esc_html($info['short']); ?></div>
  <span class="mx-kick"><i></i><?php echo esc_html($info['adv']); ?> · <?php echo esc_html($info['level']); ?></span>
  <h1><?php echo esc_html($info['title']); ?> <em>विषयवार मॉक</em></h1>
  <p><?php echo esc_html($info['post']); ?> — सभी <?php echo count($rounds); ?> विषयों के अलग-अलग मॉक। हर मॉक में 30 प्रश्न सामान्य अध्ययन के (सभी विषयों में एक जैसे) और 90 प्रश्न आपके विषय के।</p>
  <div class="mx-facts"><div><b><?php echo count($rounds); ?></b><span>विषय</span></div><div><b><?php echo $ready; ?></b><span>अभी लाइव</span></div><div><b>120</b><span>प्रश्न</span></div><div><b>+3 / −1</b><span>अंकन</span></div></div>
 </div></section>
 <section class="mx-body"><div class="mx-wrap">
  <div class="mx-head"><div><h2>अपना विषय चुनें</h2><p>विषय पर क्लिक करें, फिर लॉग-इन करके मॉक शुरू करें। लॉग-इन पेज पर भी विषय बदल सकते हैं।</p></div>
   <label class="mx-search"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input type="search" id="mx-q" placeholder="विषय खोजें — जैसे गणित, English"></label></div>
  <div class="mx-subs-wrap"><div class="mx-tabs" id="mx-tabs"><button class="on" data-f="all">सभी विषय (<?php echo count($rounds); ?>)</button><button data-f="on">लाइव (<?php echo $ready; ?>)</button><button data-f="soon">जल्द आने वाले (<?php echo count($rounds) - $ready; ?>)</button></div>
  <div class="mx-subs" id="mx-subs"><?php $i = 0; foreach ($rounds as $r): $i++; $ok = eh_tp_ready($r); ?>
   <a class="mx-s<?php echo $ok ? '' : ' soon'; ?>" href="<?php echo esc_url(eh_tp_url($r)); ?>" data-k="<?php echo esc_attr(mb_strtolower($r['sub'] . ' ' . $r['sub_en'])); ?>"><span class="ix"><?php echo str_pad((string) $i, 2, '0', STR_PAD_LEFT); ?></span><span class="tx"><b><?php echo esc_html($r['sub']); ?></b><small><?php echo esc_html($r['sub_en']); ?></small></span><span class="rt"><span class="mx-st <?php echo $ok ? 'on' : 'soon'; ?>"><?php echo $ok ? 'LIVE' : 'जल्द'; ?></span></span><span class="go"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>
  <?php endforeach; ?></div>
  <div class="mx-empty" id="mx-empty">कोई विषय नहीं मिला।</div></div>
  <div class="mx-pat">
   <div class="mx-box"><h3>प्रश्न-पत्र का स्वरूप</h3><table><tr><th>भाग</th><th>प्रश्न</th><th>अंक</th></tr><tr><td>सामान्य अध्ययन</td><td>30</td><td>90</td></tr><tr><td>संबंधित विषय</td><td>90</td><td>270</td></tr><tr><td>कुल</td><td>120</td><td>360</td></tr></table></div>
   <div class="mx-box"><h3>ज़रूरी बातें</h3><ol><li>हर सही उत्तर पर +3, गलत पर −1 अंक।</li><li>शुरू करने के बाद पूरे 120 मिनट मिलेंगे; घड़ी तभी चलती है जब परीक्षा खुली हो।</li><li>मेरिट में केवल पहला प्रयास गिना जाएगा, बाकी अभ्यास के लिए।</li><li>सबमिट के बाद स्कोर, रैंक, परसेंटाइल और व्याख्या तुरंत।</li></ol></div>
  </div>
 </div></section>
 <script>(function(){var q=document.getElementById('mx-q'),L=[].slice.call(document.querySelectorAll('#mx-subs .mx-s')),E=document.getElementById('mx-empty');F='all',run=function(){var v=q.value.trim().toLowerCase(),n=0;L.forEach(function(a){var s=(!v||a.dataset.k.indexOf(v)>-1)&&(F==='all'||(F==='soon')===a.classList.contains('soon'));a.style.display=s?'':'none';if(s)n++});E.style.display=n?'none':'block'};q.addEventListener('input',run);
[].forEach.call(document.querySelectorAll('#mx-tabs button'),function(b){b.onclick=function(){F=b.dataset.f;[].forEach.call(document.querySelectorAll('#mx-tabs button'),function(x){x.classList.toggle('on',x===b)});run()}})})();</script>
<?php endif; ?>
</main>
<?php get_footer();
