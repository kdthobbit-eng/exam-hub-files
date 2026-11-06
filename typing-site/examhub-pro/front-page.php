<?php get_header();
$exams = eh_exams(); $att = eh_attempt_counts(); $total = array_sum($att);
$popular = $exams; usort($popular, function ($a, $b) use ($att) { return ($att[$b['slug']] ?? 0) <=> ($att[$a['slug']] ?? 0); });
$recent = eh_recent(8); ?>
<section class="eh-hero">
  <div class="wrap eh-hero-in">
    <div>
      <span class="eh-kicker">100% Free · Hindi + English Typing</span>
      <h1>Government Exam की<br><em>Typing Test</em> तैयारी यहीं से</h1>
      <p class="lead">SSC, High Court, Railway, UPSSSC, DSSSB, KVS जैसे <?php echo count($exams); ?>+ एग्ज़ाम का टाइपिंग टेस्ट एक ही जगह। हिंदी में Krutidev और Mangal दोनों से प्रैक्टिस करें, टेस्ट खत्म होते ही रिज़ल्ट देखें।</p>
      <div class="eh-hero-btns">
        <a class="btn dark lg" href="<?php echo esc_url(home_url('/exams/')); ?>">All Exams देखें →</a>
        <a class="btn ghost lg" href="<?php echo esc_url(home_url('/demo-typing/')); ?>">Demo Typing</a>
      </div>
      <div class="eh-stats">
        <div><b><?php echo count($exams); ?>+</b><span>Exams</span></div>
        <div><b><?php echo count(eh_passages()); ?>+</b><span>Passages</span></div>
        <div><b><?php echo number_format_i18n(max($total, 1)); ?></b><span>Tests Taken</span></div>
      </div>
    </div>
    <div class="eh-hero-card">
      <div class="eh-mock-top"><span></span><span></span><span></span><b>09:42</b></div>
      <p class="eh-mock-text">The <i>e-Courts</i> project has connected thousands of district courts, and the daily <u>cause</u> list is now available online...</p>
      <div class="eh-mock-input">The e-Courts project has connected thousands of district courts, and the daily ca<span class="caret"></span></div>
      <div class="eh-mock-res"><span>Net <b>38 WPM</b></span><span>Accuracy <b>97.4%</b></span><span class="ok">Qualified</span></div>
    </div>
  </div>
</section>

<section class="wrap eh-section">
  <div class="eh-sec-head"><h2>Popular Exams</h2><a href="<?php echo esc_url(home_url('/exams/')); ?>">Explore all exams →</a></div>
  <div class="eh-quick">
    <?php foreach (['SSC CHSL', 'SSC CGL', 'High Court', 'UPSSSC', 'DSSSB', 'KVS', 'RRB NTPC', 'UP Police', 'Bihar', 'Rajasthan', 'Delhi Police', 'AIIMS'] as $k): ?>
      <a href="<?php echo esc_url(home_url('/exams/?q=' . rawurlencode($k))); ?>"><?php echo esc_html($k); ?></a>
    <?php endforeach; ?>
  </div>
  <div class="eh-grid">
    <?php foreach (array_slice($popular, 0, 8) as $e) echo eh_exam_card($e, $att[$e['slug']] ?? 0); ?>
  </div>
</section>

<section class="eh-band">
  <div class="wrap eh-feat">
    <div class="eh-f"><span>⌨️</span><h3>Real Exam Screen</h3><p>हर एग्ज़ाम का अपना टाइम और टारगेट। ऊपर पैसेज, नीचे टाइपिंग बॉक्स — जैसा एग्ज़ाम सेंटर में मिलता है।</p></div>
    <div class="eh-f"><span>🧮</span><h3>सही Error Calculation</h3><p>Full mistake, half mistake, key depression और net speed — सब कुछ एग्ज़ाम के नियम से गिना जाता है।</p></div>
    <div class="eh-f"><span>🇮🇳</span><h3>Krutidev + Mangal</h3><p>Remington (Krutidev) हो या Inscript (Mangal) — जिसमें आपका एग्ज़ाम है, उसी में प्रैक्टिस करें।</p></div>
    <div class="eh-f"><span>🏆</span><h3>Daily Live Typing</h3><p>रोज़ एक नया पैसेज, सबके लिए एक ही। टेस्ट दीजिए और देखिए बाकी लोगों में आपकी रैंक क्या है।</p></div>
  </div>
</section>

<section class="wrap eh-section eh-two">
  <div>
    <div class="eh-sec-head"><h2>Recent Results</h2><a href="<?php echo esc_url(home_url('/results/')); ?>">सब देखें →</a></div>
    <div class="eh-table-wrap"><table class="eh-table">
      <thead><tr><th>Name</th><th>Exam</th><th>Net</th><th>Acc.</th></tr></thead><tbody>
      <?php if (!$recent): ?><tr><td colspan="4" class="muted">अभी कोई रिज़ल्ट नहीं है — पहला टेस्ट आप ही दे दीजिए!</td></tr><?php endif; ?>
      <?php foreach ($recent as $r): $e = eh_exam($r['exam']); ?>
        <tr><td><?php echo esc_html(eh_display_name($r)); ?></td><td><?php echo esc_html($e ? $e['name'] : 'Demo Typing'); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo esc_html($r['acc']); ?>%</td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
  <div class="eh-why">
    <h2>Exam Hub Typing ही क्यों?</h2>
    <ul>
      <li><b>पूरी तरह Free</b> — न कोई फीस, न कोई प्लान।</li>
      <li><b>एक अकाउंट, सारे एग्ज़ाम</b> — अलग-अलग जगह जाने की ज़रूरत नहीं।</li>
      <li><b>Exam Pattern</b> — हर एग्ज़ाम का टाइम और टारगेट उसके नियम के हिसाब से।</li>
      <li><b>अपनी Progress देखें</b> — लॉगिन करें, आपके सारे रिज़ल्ट सेव रहेंगे।</li>
      <li><b>Telegram पर मदद</b> — कोई दिक्कत हो तो सीधे ग्रुप में पूछिए।</li>
    </ul>
    <?php echo eh_tg_buttons(); ?>
  </div>
</section>

<section class="wrap eh-section">
  <h2>FAQs</h2>
  <div class="eh-faq">
    <details><summary>क्या ये टाइपिंग टेस्ट फ्री है?</summary><p>हाँ, सारे टेस्ट, लाइव टाइपिंग और रिज़ल्ट बिल्कुल फ्री हैं।</p></details>
    <details><summary>Krutidev में टाइप कैसे करें?</summary><p>टेस्ट शुरू करने से पहले Layout में "Krutidev / Remington GAIL" चुन लीजिए। कीबोर्ड English पर ही रहने दें, आप जो टाइप करेंगे वो अपने-आप हिंदी में दिखेगा।</p></details>
    <details><summary>Full और Half mistake में क्या फर्क है?</summary><p>गलत शब्द, छूटा शब्द या फालतू शब्द — ये Full mistake हैं। स्पेस, कैपिटल लेटर या कॉमा-फुलस्टॉप की गलती Half mistake गिनी जाती है।</p></details>
    <details><summary>मोबाइल पर टेस्ट दे सकते हैं?</summary><p>English टेस्ट मोबाइल पर चल जाता है, लेकिन असली प्रैक्टिस के लिए कंप्यूटर या लैपटॉप ही ठीक रहता है।</p></details>
  </div>
</section>
<?php get_footer();
