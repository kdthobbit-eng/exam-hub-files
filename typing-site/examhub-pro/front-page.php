<?php get_header();
$exams = eh_exams(); $att = eh_attempt_counts(); $total = array_sum($att);
$popular = $exams; usort($popular, function ($a, $b) use ($att) { return ($att[$b['slug']] ?? 0) <=> ($att[$a['slug']] ?? 0); });
$recent = eh_recent(8); ?>
<section class="eh-hero">
  <div class="wrap eh-hero-in">
    <div>
      <span class="eh-kicker">100% Free · Hindi & English</span>
      <h1>असली परीक्षा जैसा<br><em>टाइपिंग टेस्ट</em> — हर सरकारी परीक्षा के लिए</h1>
      <p class="lead"><?php echo esc_html(eh_opt('tagline')); ?>. SSC, High Court, Railway, UPSSSC, DSSSB, KVS समेत <?php echo count($exams); ?>+ परीक्षाओं का पैटर्न, Krutidev और Mangal दोनों में।</p>
      <div class="eh-hero-btns">
        <a class="btn dark lg" href="<?php echo esc_url(home_url('/exams/')); ?>">सभी परीक्षाएं देखें →</a>
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
    <div class="eh-f"><span>⌨️</span><h3>Exam Interface</h3><p>हर परीक्षा का समय, keys/words का लक्ष्य, बैकस्पेस और हाइलाइट सेटिंग — असली परीक्षा जैसा।</p></div>
    <div class="eh-f"><span>🧮</span><h3>Error Formula</h3><p>Full mistake, half mistake (spacing, capital, punctuation), key depressions और net speed का सटीक हिसाब।</p></div>
    <div class="eh-f"><span>🇮🇳</span><h3>Krutidev + Mangal</h3><p>Remington GAIL (Krutidev) और Inscript (Mangal) — दोनों लेआउट में हिंदी टाइपिंग।</p></div>
    <div class="eh-f"><span>🏆</span><h3>Daily Live Typing</h3><p>रोज़ एक नया पैसेज, सबके लिए एक जैसा — अपनी रैंक देखें और दूसरों से मुकाबला करें।</p></div>
  </div>
</section>

<section class="wrap eh-section eh-two">
  <div>
    <div class="eh-sec-head"><h2>Recent Results</h2><a href="<?php echo esc_url(home_url('/results/')); ?>">सब देखें →</a></div>
    <div class="eh-table-wrap"><table class="eh-table">
      <thead><tr><th>Name</th><th>Exam</th><th>Net</th><th>Acc.</th></tr></thead><tbody>
      <?php if (!$recent): ?><tr><td colspan="4" class="muted">अभी कोई रिज़ल्ट नहीं — पहला टेस्ट आप दीजिए!</td></tr><?php endif; ?>
      <?php foreach ($recent as $r): $e = eh_exam($r['exam']); ?>
        <tr><td><?php echo esc_html(eh_display_name($r)); ?></td><td><?php echo esc_html($e ? $e['name'] : 'Demo Typing'); ?></td><td><b><?php echo (int) $r['net']; ?></b></td><td><?php echo esc_html($r['acc']); ?>%</td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
  <div class="eh-why">
    <h2>Why Exam Hub Typing?</h2>
    <ul>
      <li><b>Free start</b> — कोई फीस नहीं, कोई ज़बरदस्ती सब्सक्रिप्शन नहीं।</li>
      <li><b>One platform</b> — एक अकाउंट, सारी परीक्षाएं।</li>
      <li><b>Exam pattern</b> — हर परीक्षा के नियम के हिसाब से समय और लक्ष्य।</li>
      <li><b>Progress</b> — लॉगिन करें और अपना पूरा रिज़ल्ट इतिहास देखें।</li>
      <li><b>Telegram support</b> — सवाल और फीडबैक सीधे ग्रुप में।</li>
    </ul>
    <?php echo eh_tg_buttons(); ?>
  </div>
</section>

<section class="wrap eh-section">
  <h2>FAQs</h2>
  <div class="eh-faq">
    <details><summary>क्या यह टाइपिंग टेस्ट फ्री है?</summary><p>हाँ, सभी टेस्ट, लाइव टाइपिंग और रिज़ल्ट पूरी तरह फ्री हैं।</p></details>
    <details><summary>Krutidev में कैसे टाइप करें?</summary><p>टेस्ट शुरू करते समय Layout में "Krutidev / Remington GAIL" चुनें। अपने कंप्यूटर का कीबोर्ड English पर रखें — टाइप किया हुआ अपने-आप हिंदी में बदल जाएगा।</p></details>
    <details><summary>Full और Half mistake क्या है?</summary><p>गलत, छूटा हुआ या अतिरिक्त शब्द Full mistake है। स्पेसिंग, कैपिटल लेटर या विराम-चिह्न की गलती Half mistake मानी जाती है।</p></details>
    <details><summary>क्या मोबाइल पर टेस्ट दे सकते हैं?</summary><p>English टेस्ट मोबाइल पर चलता है, लेकिन असली अभ्यास के लिए कंप्यूटर/लैपटॉप कीबोर्ड सबसे अच्छा है।</p></details>
  </div>
</section>
<?php get_footer();
