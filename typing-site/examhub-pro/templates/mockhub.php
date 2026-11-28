<?php
/* Live Mock hub: Super TET, UP TGT, UP PGT. TGT/PGT: the student picks a subject once (saved on the server, changeable any time),
   then lands in that subject's test zone: full mock, sectional mocks, topic-wise sets and previous-year papers. */
if (!defined('ABSPATH')) exit;
nocache_headers();
$x = sanitize_key($_GET['x'] ?? '');
$stet = $x === 'stet';
if (!in_array($x, ['tgt', 'pgt'], true)) $x = '';
$sub = $x ? sanitize_key($_GET['s'] ?? '') : '';
if ($sub && !isset(eh_tp_subjects()[$x][$sub])) $sub = '';
$uid = get_current_user_id();
if ($x && !$sub && empty($_GET['all']) && ($p = eh_tp_pref($uid, $x))) { wp_safe_redirect(add_query_arg(['x' => $x, 's' => $p], home_url('/mock-test/'))); exit; }
$info = $x ? eh_tp_info($x) : null;
$rounds = $x ? array_values(array_filter(eh_tp_rounds(), function ($r) use ($x) { return $r['series'] === $x; })) : [];
$hub = home_url('/mock-test/');
get_header();
$hub = home_url('/mock-test/');
?>
<main class="mx">
<?php if ($stet):
  $SR = array_values(array_filter(eh_stm_rounds(), function ($r) { return ($r['series'] ?? 'stet') === 'stet' && $r['id'][0] === 'm'; }));
  $SP = array_values(array_filter(eh_stm_rounds(), function ($r) { return ($r['kind'] ?? '') === 'practice'; })); $my = eh_tp_my($uid);
  $u = wp_get_current_user(); $nmU = $uid ? ($u->display_name ?: $u->user_login) : ''; $ini = $nmU ? mb_strtoupper(mb_substr(trim($nmU), 0, 1)) : '';
  $done = count(array_filter($SR, function ($r) use ($my) { return !empty($my[$r['id']]['n']); })); ?>
 <section class="mz-bar"><div class="mx-wrap">
  <div class="mz-crumb"><a href="<?php echo esc_url($hub); ?>">← Live Mock</a></div>
  <div class="zh">
   <div class="zh-u"><?php if ($uid): ?><span class="zh-av"><?php echo esc_html($ini); ?></span><div><small>नमस्ते,</small><b><?php echo esc_html($nmU); ?></b></div><?php else: ?><span class="zh-av">👋</span><div><small>नमस्ते!</small><b><a href="<?php echo esc_url(eh_login_page_url(add_query_arg('x', 'stet', $hub))); ?>">लॉग-इन / साइन-अप करें</a></b></div><?php endif; ?></div>
   <div class="zh-x"><span class="mx-kick"><i></i>सहायक अध्यापक भर्ती · प्राथमिक स्तर (कक्षा 1–5)</span><h1>UP Super <em>TET</em> 2026 <small>टेस्ट सीरीज़</small></h1></div>
   <div class="zh-r"><span class="zh-chip">📘 UP Super TET</span><span class="zh-st"><b><?php echo $done; ?></b>/<?php echo count($SR); ?> टेस्ट दिए</span></div>
  </div>
  <nav class="rb" id="mz-tabs"><button class="on" data-t="prac"><i>📝</i>फुल लेंथ मॉक<em><?php echo count($SP); ?></em></button><button data-t="full"><i>📄</i>लाइव मॉक<em><?php echo count($SR); ?></em></button><button data-t="pyq"><i>🗂️</i>PYQ<em>Coming Soon</em></button></nav>
 </div></section>
 <section class="mx-body"><div class="mx-wrap">
  <div class="mz-p on" data-p="prac"><div class="tl-h"><b>फुल लेंथ मॉक (प्रैक्टिस)</b><span>हमेशा खुले — कभी भी दें; 120 प्रश्न, नए पैटर्न पर, पूरा हल और रैंक</span></div>
   <div class="tl"><?php $n = 0; foreach ($SP as $r) { $n++; echo eh_tp_test_row($r, $my, $n, $r['name']); } ?></div></div>
  <div class="mz-p" data-p="full"><div class="tl-h"><b>Super TET लाइव मॉक टेस्ट</b><span>तय तारीख पर खुलने वाले लाइव मॉक — पूरा प्रश्न-पत्र, रैंक, परसेंटाइल और उत्तर-कुंजी</span></div>
   <div class="tl"><?php $n = 0; foreach ($SR as $r) { $n++; echo eh_tp_test_row($r, $my, $n, 'SUPER TET - LIVE MOCK ' . $n); } ?></div></div>
  <div class="mz-p" data-p="pyq"><div class="mz-empty"><b>🗂️ पिछले वर्ष के प्रश्न-पत्र — Coming Soon</b><p>UP Super TET के असली पिछले प्रश्न-पत्र, उत्तर-कुंजी और व्याख्या के साथ।</p></div></div>
 </div></section>
 <script>(function(){var B=[].slice.call(document.querySelectorAll('#mz-tabs button')),P=[].slice.call(document.querySelectorAll('.mz-p'));B.forEach(function(b){b.onclick=function(){B.forEach(function(x){x.classList.toggle('on',x===b)});P.forEach(function(p){p.classList.toggle('on',p.dataset.p===b.dataset.t)})}})})();</script>
<?php elseif (!$x): ?>
 <section class="mx-hero"><div class="mx-wrap">
  <?php if ($uid): $uu = wp_get_current_user(); ?><div class="hi-u"><span class="zh-av"><?php echo esc_html(mb_strtoupper(mb_substr(trim($uu->display_name ?: $uu->user_login), 0, 1))); ?></span>नमस्ते, <b><?php echo esc_html($uu->display_name ?: $uu->user_login); ?></b> 👋</div><?php endif; ?>
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
  <div class="mx-subs-wrap"><div class="mx-tabs" id="mx-tabs"><button class="on" data-f="all">सभी विषय (<?php echo count($rounds); ?>)</button><button data-f="on">लाइव (<?php echo $ready; ?>)</button><button data-f="soon">Coming Soon (<?php echo count($rounds) - $ready; ?>)</button></div>
  <div class="mx-subs" id="mx-subs"><?php $i = 0; $pf = eh_tp_pref($uid, $x); foreach ($rounds as $r): $i++; $ok = eh_tp_ready($r); ?>
   <a class="mx-s<?php echo $ok ? '' : ' soon'; ?><?php echo $pf === $r['slug'] ? ' mine' : ''; ?>" data-sub="<?php echo esc_attr($r['slug']); ?>" data-tp-pick="<?php echo esc_attr($x); ?>" href="<?php echo esc_url(add_query_arg(['x' => $x, 's' => $r['slug']], $hub)); ?>" data-k="<?php echo esc_attr(mb_strtolower($r['sub'] . ' ' . $r['sub_en'])); ?>"><span class="ix"><?php echo str_pad((string) $i, 2, '0', STR_PAD_LEFT); ?></span><span class="tx"><b><?php echo esc_html($r['sub']); ?></b><small><?php echo esc_html($r['sub_en']); ?></small></span><span class="rt"><span class="mx-st <?php echo $ok ? 'on' : 'soon'; ?>"><?php echo $pf === $r['slug'] ? 'आपका विषय' : ($ok ? 'LIVE' : 'Coming Soon'); ?></span></span><span class="go"><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>
  <?php endforeach; ?></div>
  <div class="mx-empty" id="mx-empty">कोई विषय नहीं मिला।</div></div>
 </div></section>
 <script>(function(){var X=<?php echo wp_json_encode($x); ?>,ALL=/[?&]all=1/.test(location.search),IN=<?php echo $uid ? 'true' : 'false'; ?>;
 try{var p=localStorage.getItem('eh_tps_'+X);if(p&&!ALL&&!IN){location.replace(location.pathname+'?x='+X+'&s='+p);return}}catch(e){}
 var q=document.getElementById('mx-q'),L=[].slice.call(document.querySelectorAll('#mx-subs .mx-s')),E=document.getElementById('mx-empty'),F='all',run=function(){var v=q.value.trim().toLowerCase(),n=0;L.forEach(function(a){var s=(!v||a.dataset.k.indexOf(v)>-1)&&(F==='all'||(F==='soon')===a.classList.contains('soon'));a.style.display=s?'':'none';if(s)n++});E.style.display=n?'none':'block'};q.addEventListener('input',run);
 [].forEach.call(document.querySelectorAll('#mx-tabs button'),function(b){b.onclick=function(){F=b.dataset.f;[].forEach.call(document.querySelectorAll('#mx-tabs button'),function(x){x.classList.toggle('on',x===b)});run()}})})();</script>
<?php else:
  $R = eh_tp_find(($x === 'tgt' ? 'tg' : 'pg') . str_pad((string) (array_search($sub, array_keys(eh_tp_subjects()[$x])) + 1), 2, '0', STR_PAD_LEFT));
  $RS = eh_tp_find($R['id'] . 's'); $GS = eh_tp_find($R['id'] . 'x'); $my = eh_tp_my($uid); $nm = eh_tp_subjects()[$x][$sub];
  $lang = in_array($sub, ['hindi', 'english', 'sanskrit', 'urdu'], true) ? $nm[0] : 'हिंदी + English';
  $FL = eh_tp_flts($R); $FULL = $FL ?: [$R];
  $TL = eh_tp_topics()[$x][$sub] ?? []; $GT = eh_tp_gs_topics();
  $ids = array_merge(array_column($FULL, 'id'), [$RS['id'], $GS['id']]);
  foreach ($TL as $i => $tn) if (eh_tp_find($R['id'] . 't' . ($i + 1))) $ids[] = $R['id'] . 't' . ($i + 1);
  foreach ($GT as $i => $tn) if (eh_tp_find($R['id'] . 'g' . ($i + 1))) $ids[] = $R['id'] . 'g' . ($i + 1);
  $done = count(array_filter($ids, function ($id) use ($my) { return !empty($my[$id]['n']); })); $tot = count($ids);
  $u = wp_get_current_user(); $nmU = $uid ? ($u->display_name ?: $u->user_login) : '';
  $ini = $nmU ? mb_strtoupper(mb_substr(trim($nmU), 0, 1)) : ''; ?>
 <section class="mz-bar"><div class="mx-wrap">
  <div class="mz-crumb"><a href="<?php echo esc_url($hub); ?>">← Live Mock</a></div>
  <div class="zh">
   <div class="zh-u"><?php if ($uid): ?><span class="zh-av"><?php echo esc_html($ini); ?></span><div><small>नमस्ते,</small><b><?php echo esc_html($nmU); ?></b></div><?php else: ?><span class="zh-av">👋</span><div><small>नमस्ते!</small><b><a href="<?php echo esc_url(eh_login_page_url(add_query_arg(['x' => $x, 's' => $sub], $hub))); ?>">लॉग-इन / साइन-अप करें</a></b></div><?php endif; ?></div>
   <div class="zh-x"><span class="mx-kick"><i></i><?php echo esc_html($info['title'] . ' · ' . $info['adv']); ?></span><h1><?php echo esc_html($info['short']); ?> <em><?php echo esc_html($nm[0]); ?></em> <small>टेस्ट सीरीज़</small></h1></div>
   <div class="zh-r"><a class="zh-chip" data-tp="<?php echo esc_attr($x); ?>" data-tp-change href="<?php echo esc_url(add_query_arg(['x' => $x, 'all' => 1], $hub)); ?>">📘 <?php echo esc_html($info['short'] . ' · ' . $nm[0]); ?> <em>बदलें ✎</em></a><span class="zh-st"><b><?php echo $done; ?></b>/<?php echo $tot; ?> टेस्ट दिए</span></div>
  </div>
  <nav class="rb" id="mz-tabs"><button class="on" data-t="full"><i>📄</i>फुल मॉक<em><?php echo count($FULL); ?></em></button><button data-t="sec"><i>🧩</i>सेक्शनल<em>2</em></button><button data-t="topic"><i>🎯</i>टॉपिक-वाइज<em><?php echo count($TL) + count($GT); ?></em></button><button data-t="pyq"><i>🗂️</i>PYQ<em>Coming Soon</em></button></nav>
 </div></section>
 <section class="mx-body"><div class="mx-wrap">
  <div class="mz-p on" data-p="full"><div class="tl-h"><b>फुल मॉक टेस्ट</b><span>असली परीक्षा जैसा पूरा प्रश्न-पत्र — 30 सामान्य अध्ययन + 90 <?php echo esc_html($nm[0]); ?></span></div>
   <div class="tl"><?php foreach ($FULL as $i => $F) echo eh_tp_test_row($F, $my, $i + 1, $F['name'], $lang); ?></div></div>
  <div class="mz-p" data-p="sec"><div class="tl-h"><b>सेक्शनल मॉक टेस्ट</b><span>एक भाग का अलग अभ्यास, कम समय में</span></div>
   <div class="tl"><?php echo eh_tp_test_row($RS, $my, 1, $RS['name'], $lang) . eh_tp_test_row($GS, $my, 2, $GS['name'], 'हिंदी + English'); ?></div></div>
  <div class="mz-p" data-p="topic"><div class="tl-h"><b>टॉपिक-वाइज टेस्ट</b><span>आधिकारिक पाठ्यक्रम के हर टॉपिक पर 50 प्रश्नों का सेट, विस्तृत हल के साथ</span></div>
   <div class="tsw" id="tsw" role="tablist"><button<?php echo $TL ? ' class="on"' : ''; ?> data-s="sub" type="button"><span>📘 <?php echo esc_html($nm[0]); ?></span><em><?php echo count($TL) ?: '—'; ?></em></button><button<?php echo $TL ? '' : ' class="on"'; ?> data-s="gs" type="button"><span>🌐 सामान्य अध्ययन</span><em><?php echo count($GT); ?></em></button></div>
   <div class="tsp<?php echo $TL ? ' on' : ''; ?>" data-s="sub"><?php if ($TL): ?><div class="tl">
   <?php foreach ($TL as $i => $tn): $TR = eh_tp_find($R['id'] . 't' . ($i + 1));
     if ($TR) echo eh_tp_test_row($TR, $my, $i + 1, $TR['name'], $lang);
     else echo '<div class="tr soon"><div class="tr-n">' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . '</div><div class="tr-b"><div class="tr-t"><b>' . esc_html(strtoupper($x) . ' ' . eh_tp_en($sub, $nm[1]) . ' - TOPIC TEST ' . ($i + 1)) . '</b><span class="tr-soon">Coming Soon</span></div>' . (!empty($tn[1]) ? '<div class="tr-tp">' . esc_html($tn[1]) . '</div>' : '') . '<div class="tr-m"><span>📝 50 प्रश्न</span></div></div><div class="tr-a"><span class="tr-btn dis">Coming Soon</span></div></div>';
   endforeach; ?></div>
   <?php else: ?><div class="mz-empty"><b>🎯 <?php echo esc_html($nm[0]); ?> टॉपिक-वाइज टेस्ट — Coming Soon</b><p><?php echo esc_html($nm[0]); ?> के हर टॉपिक पर 50-50 प्रश्नों के सेट जल्द आएँगे। तब तक सामान्य अध्ययन के टॉपिक टेस्ट दें।</p></div><?php endif; ?></div>
   <div class="tsp<?php echo $TL ? '' : ' on'; ?>" data-s="gs">
   <?php $UN = [[0, 0, 'इकाई 1', 'समसामयिक घटनाएँ'], [1, 6, 'इकाई 2', 'इतिहास, संविधान, शिक्षा विधि, अर्थव्यवस्था, भूगोल व उत्तर प्रदेश'], [7, 9, 'इकाई 3', 'विज्ञान, स्वास्थ्य एवं कंप्यूटर / ICT']];
   foreach ($UN as $u): ?><div class="tu-h"><b><?php echo esc_html($u[2]); ?></b><span><?php echo esc_html($u[3]); ?></span></div><div class="tl">
   <?php for ($i = $u[0]; $i <= $u[1]; $i++): $TR = eh_tp_find($R['id'] . 'g' . ($i + 1)); if ($TR) echo eh_tp_test_row($TR, $my, $i + 1, $TR['name'], 'हिंदी + English'); endfor; ?></div>
   <?php endforeach; ?></div>
  </div>
  <div class="mz-p" data-p="pyq"><div class="mz-empty"><b>🗂️ पिछले वर्ष के प्रश्न-पत्र — Coming Soon</b><p>UPESSC / UPSESSB के असली पिछले प्रश्न-पत्र, उत्तर-कुंजी और व्याख्या के साथ।</p></div></div>
 </div></section>
 <script>(function(){var W=[].slice.call(document.querySelectorAll('#tsw button')),SP=[].slice.call(document.querySelectorAll('.tsp'));W.forEach(function(b){b.onclick=function(){W.forEach(function(x){x.classList.toggle('on',x===b)});SP.forEach(function(p){p.classList.toggle('on',p.dataset.s===b.dataset.s)})}})})();</script>
 <script>(function(){var B=[].slice.call(document.querySelectorAll('#mz-tabs button')),P=[].slice.call(document.querySelectorAll('.mz-p'));B.forEach(function(b){b.onclick=function(){B.forEach(function(x){x.classList.toggle('on',x===b)});P.forEach(function(p){p.classList.toggle('on',p.dataset.p===b.dataset.t)});try{b.scrollIntoView({block:'nearest',inline:'center',behavior:'smooth'})}catch(e){}}})})();</script>
<?php endif; ?>
</main>
<script>window.addEventListener('pageshow',function(e){if(e.persisted)location.reload()});</script>
<?php echo eh_tp_picker(); if ($x && !$sub): ?><script>if(window.ehTpOpen)ehTpOpen(<?php echo wp_json_encode($x); ?>)</script><?php endif; ?>
<?php get_footer();
