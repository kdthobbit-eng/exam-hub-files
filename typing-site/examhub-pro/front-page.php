<?php get_header();
$exams = eh_exams(); $att = eh_attempt_counts();
$popular = $exams; usort($popular, function ($a, $b) use ($att) { return ($att[$b['slug']] ?? 0) <=> ($att[$a['slug']] ?? 0); });
$recent = eh_recent(100);
$popular = eh_ja_first($popular);

/* photos: free Unsplash licence, loaded from Unsplash's image server */
$ph = function ($id, $w = 1400) { return 'https://images.unsplash.com/photo-' . $id . '?auto=format&fit=crop&q=70&w=' . (int) $w; };
$P = [
  'hero'   => '1567168539593-59673ababaae',
  'mech'   => '1567093322102-6bdd32fba67d',
  'class'  => '1514369118554-e20d93546b30',
  'keys'   => '1556505619-560ec2cb7c94',
  'books'  => '1517673132405-a56a62b18caf',
];

/* running batches: details as posted on the UPSSSC Alert Telegram channel */
$batches = [
  ['UP JE', 499, ['UPSSSC JE Mechanical', 'UPSSSC JE मैकेनिकल'], ['Complete preparation batch', 'कम्प्लीट प्रिपरेशन बैच'], [
    ['Mechanical Engineering: 65 questions', 'Mechanical Engineering – 65 प्रश्न'],
    ['Computer &amp; IT: 15 questions', 'Computer &amp; IT – 15 प्रश्न'],
    ['UP GK: 20 questions', 'UP GK – 20 प्रश्न'],
    ['Complete notes in Hindi and English', 'पूरे नोट्स, हिंदी और अंग्रेजी दोनों में'],
    ['Daily topic-wise quiz', 'रोज़ टॉपिक-वाइज क्विज'],
    ['10 full-length mock tests', '10 फुल लेंथ मॉक टेस्ट'],
    ['20 sectional tests', '20 सेक्शनल टेस्ट'],
  ]],
  ['VDO / VPO', 199, ['VDO / VPO Batch', 'VDO / VPO बैच'], ['Notes, quiz and tests', 'नोट्स, क्विज और टेस्ट'], [
    ['Exam-oriented notes from syllabus and PYQ analysis', 'सिलेबस और PYQ पर आधारित नोट्स'],
    ['Booster PDFs and latest current affairs', 'बूस्टर PDF और लेटेस्ट करेंट अफेयर्स'],
    ['10,000+ quiz and 50+ tests', '10,000+ क्विज और 50+ टेस्ट'],
    ['Short explainer videos for hard topics', 'कठिन टॉपिक के लिए छोटे वीडियो'],
  ]],
  ['UP Police', 179, ['Computer Operator Batch', 'कंप्यूटर ऑपरेटर बैच'], ['GS + Computer Science', 'GS + कंप्यूटर साइंस'], [
    ['Special GS PDF', 'GS की स्पेशल PDF'],
    ['Computer Science PDF as per the official syllabus', 'ऑफिशियल सिलेबस के अनुसार कंप्यूटर साइंस PDF'],
    ['6,000+ topic-wise quiz', '6,000+ टॉपिक-वाइज क्विज'],
    ['10 GS tests + 10 Computer Science tests', 'GS के 10 और कंप्यूटर साइंस के 10 टेस्ट'],
  ]],
  ['UP TGT / PGT', 149, ['GS Booster Batch', 'GS बूस्टर बैच'], ['For the 90-mark GS part', '90 अंक के GS के लिए'], [
    ['Notes e-book for all important subjects', 'सभी ज़रूरी विषयों की नोट्स ई-बुक'],
    ['5,000+ subject-wise practice quiz', '5,000+ विषयवार प्रैक्टिस क्विज'],
    ['10 tests', '10 टेस्ट'],
  ]],
  ['Super TET', 199, ['Acharya 2.0', 'आचार्य 2.0'], ['Rapid revision batch', 'रैपिड रिवीजन बैच'], [
    ['PDF notes for all subjects', 'सभी विषयों के PDF नोट्स'],
    ['Topic-wise and mixed practice quiz', 'टॉपिक-वाइज और मिक्स प्रैक्टिस क्विज'],
    ['10 full-length tests', '10 फुल लेंथ टेस्ट'],
    ['Previous year questions (PYQ)', 'पिछले वर्षों के प्रश्न (PYQ)'],
  ]],
];
$join = eh_opt('tg_join');
$wa = function ($b) { return eh_wa_url('Hello, I want to join the ' . $b . ' batch.'); };
$arrow = '<svg class="uh-ar" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
?>
<script>try{if(localStorage.getItem('eh_lang')==='en')document.documentElement.setAttribute('data-lang','en')}catch(e){}</script>
<div class="uh">

<section class="uh-hero">
  <div class="wrap uh-hero-in">
    <img class="uh-hero-bg" src="<?php echo esc_url($ph($P['hero'], 1200)); ?>" alt="" fetchpriority="high">
    <div class="uh-lang" role="group" aria-label="Language"><button type="button" data-lang="hi">हिंदी</button><button type="button" data-lang="en">English</button></div>
    <p class="uh-eyebrow"><?php echo eh_t('UPSSSC and UP exams · Courses · Mock tests · Typing', 'UPSSSC और UP की भर्तियाँ · कोर्स · मॉक टेस्ट · टाइपिंग'); ?></p>
    <h1 class="uh-h1"><?php echo eh_t('<span>Your home</span> <span><em>before the job.</em></span>', '<span>नौकरी लगने से</span> <span>पहले का <em>घर।</em></span>'); ?></h1>
    <p class="uh-lead"><?php echo eh_t('Notes, daily quiz, mock tests and exam-style typing practice for UPSSSC and other UP exams. All in one place.', 'UPSSSC और UP की बाकी भर्तियों के लिए नोट्स, रोज़ क्विज, मॉक टेस्ट और एग्जाम जैसी टाइपिंग प्रैक्टिस। सब एक जगह।'); ?></p>
    <div class="uh-cta">
      <a class="uh-btn" href="#batches"><?php echo eh_t('See running batches', 'चल रहे बैच देखें'); ?><?php echo $arrow; ?></a>
      <a class="uh-btn" href="<?php echo esc_url(home_url('/live-typing/')); ?>"><?php echo eh_t('Junior Assistant Live Typing', 'जूनियर असिस्टेंट लाइव टाइपिंग'); ?><?php echo $arrow; ?></a>
    </div>
    <ul class="uh-stats">
      <li><b data-count="60000" data-suffix="+">60,000+</b><span><?php echo eh_t('students with us', 'विद्यार्थी हमारे साथ'); ?></span></li>
      <li><b data-count="6000" data-suffix="+">6,000+</b><span><?php echo eh_t('students joined our batches', 'विद्यार्थी हमारे बैच से जुड़े'); ?></span></li>
      <li><b data-count="<?php echo count($exams); ?>"><?php echo count($exams); ?></b><span><?php echo eh_t('exam typing tests', 'एग्जाम टाइपिंग टेस्ट'); ?></span></li>
      <li><b>₹0</b><span><?php echo eh_t('for all typing practice', 'पूरी टाइपिंग प्रैक्टिस के लिए'); ?></span></li>
    </ul>
  </div>
</section>

<div class="uh-marq" aria-hidden="true"><div class="uh-marq-in">
  <?php $mq = ['UPSSSC JE', 'VDO / VPO', 'UP Police Computer Operator', 'UP TGT / PGT', 'Super TET', 'AHC RO / ARO', 'Lekhpal', 'Stenographer', 'Junior Assistant', 'UPSI'];
  for ($k = 0; $k < 2; $k++) foreach ($mq as $m) echo '<span>' . esc_html($m) . '</span><i>✦</i>'; ?>
</div></div>

<section class="uh-sec" id="batches">
  <div class="wrap">
    <div class="uh-head" data-rv>
      <p class="uh-kick"><?php echo eh_t('Running batches', 'चल रहे बैच'); ?></p>
      <h2 class="uh-h2"><?php echo eh_t('Pick your exam. <em>We bring the notes, quiz and tests.</em>', 'अपनी परीक्षा चुनिए। <em>नोट्स, क्विज और टेस्ट हम देंगे।</em>'); ?></h2>
      <p class="uh-sub"><?php echo eh_t('Every batch runs on Telegram. To join, message us on Telegram or WhatsApp.', 'हर बैच टेलीग्राम पर चलता है। जुड़ने के लिए टेलीग्राम या व्हाट्सऐप पर मैसेज करें।'); ?></p>
    </div>

    <?php $f = $batches[0]; ?>
    <article class="uh-feat" data-rv>
      <div class="uh-feat-img"><img src="<?php echo esc_url($ph($P['mech'], 1200)); ?>" alt="" loading="lazy"><span class="uh-new"><?php echo eh_t('New batch', 'नया बैच'); ?></span></div>
      <div class="uh-feat-txt">
        <p class="uh-tag"><?php echo esc_html($f[0]); ?></p>
        <h3><?php echo eh_t($f[2][0], $f[2][1]); ?> <em><?php echo eh_t($f[3][0], $f[3][1]); ?></em></h3>
        <ul class="uh-list two"><?php foreach ($f[4] as $x) echo '<li>' . eh_t($x[0], $x[1]) . '</li>'; ?></ul>
        <div class="uh-feat-foot">
          <p class="uh-fee"><small><?php echo eh_t('Batch fee', 'बैच फीस'); ?></small>₹<?php echo (int) $f[1]; ?></p>
          <a class="uh-btn dark" href="<?php echo esc_url($join); ?>" target="_blank" rel="noopener"><?php echo eh_t('Join on Telegram', 'टेलीग्राम पर जुड़ें'); ?><?php echo $arrow; ?></a>
          <a class="uh-btn line" href="<?php echo esc_url($wa($f[2][0])); ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
      </div>
    </article>

    <?php /* UPSSSC Alert's own book (details as posted in the UPSSSC Alert Telegram group) */
    $bk_img = file_exists(EH_DIR . '/assets/book-supertet.jpg') ? EH_URI . '/assets/book-supertet.jpg' : '';
    $bk_tg = 'https://t.me/Booksalert2026'; $bk_wa = 'https://wa.me/918726821839?text=' . rawurlencode('UP SUPER TET BOOK order'); ?>
    <article class="uh-feat uh-book" data-rv>
      <div class="uh-feat-txt">
        <p class="uh-tag"><?php echo eh_t('Book · UP SUPER TET', 'किताब · UP SUPER TET'); ?></p>
        <h3><?php echo eh_t('UP SUPER TET Book', 'UP SUPER TET बुक'); ?> <em><?php echo eh_t('First edition 2026 · by Prateek Tiwari', 'प्रथम संस्करण 2026 · प्रतीक तिवारी'); ?></em></h3>
        <ul class="uh-list two">
          <li><?php echo eh_t('Assistant Teacher (Class 1-5), new syllabus', 'सहायक अध्यापक (कक्षा 1-5), नया पाठ्यक्रम'); ?></li>
          <li><?php echo eh_t('Chapter-wise theory, tricks and one-liners', 'अध्यायवार थ्योरी, ट्रिक्स और वन-लाइनर'); ?></li>
          <li><?php echo eh_t('6,000+ MCQ, solved PYQs and 1 exam-level model paper', '6,000+ MCQ, हल किए पिछले प्रश्न और 1 मॉडल पेपर'); ?></li>
          <li><?php echo eh_t('All subjects for the 360-mark paper, NEP 2020 and NCF 2023 aligned', '360 अंक के पेपर के सभी विषय, NEP 2020 और NCF 2023 के अनुसार'); ?></li>
          <li><?php echo eh_t('Home delivery across India in 2-3 days', 'पूरे भारत में 2-3 दिन में घर तक डिलीवरी'); ?></li>
          <li><?php echo eh_t('Also at book shops in Ayodhya, Barabanki, Banda, Fatehpur and Mahoba', 'अयोध्या, बाराबंकी, बांदा, फतेहपुर और महोबा की दुकानों पर भी'); ?></li>
        </ul>
        <div class="uh-feat-foot">
          <p class="uh-fee"><small><?php echo eh_t('Book price', 'किताब की कीमत'); ?></small>₹429</p>
          <a class="uh-btn dark" href="<?php echo esc_url($bk_tg); ?>" target="_blank" rel="noopener"><?php echo eh_t('Order on Telegram', 'टेलीग्राम पर ऑर्डर करें'); ?><?php echo $arrow; ?></a>
          <a class="uh-btn line" href="<?php echo esc_url($bk_wa); ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
      </div>
      <div class="uh-feat-img uh-book-img"><?php if ($bk_img): ?><img src="<?php echo esc_url($bk_img); ?>" alt="UP SUPER TET Book by UPSSSC Alert" loading="lazy"><?php else: ?>
        <div class="uh-book-cover"><span>UPSSSC ALERT</span><b>UP SUPER TET</b><i>6000+ <?php echo eh_t('questions', 'प्रश्न'); ?> · <?php echo eh_t('theory', 'थ्योरी'); ?> · <?php echo eh_t('model papers', 'मॉडल पेपर'); ?></i></div><?php endif; ?>
        <span class="uh-new"><?php echo eh_t('Our book', 'हमारी किताब'); ?></span></div>
    </article>

    <div class="uh-bgrid">
      <?php foreach (array_slice($batches, 1) as $i => $b): ?>
      <article class="uh-b" data-rv style="--d:<?php echo $i * 80; ?>ms">
        <p class="uh-tag"><?php echo esc_html($b[0]); ?></p>
        <h3><?php echo eh_t($b[2][0], $b[2][1]); ?></h3>
        <p class="uh-b-sub"><?php echo eh_t($b[3][0], $b[3][1]); ?></p>
        <ul class="uh-list"><?php foreach ($b[4] as $x) echo '<li>' . eh_t($x[0], $x[1]) . '</li>'; ?></ul>
        <div class="uh-b-foot"><p class="uh-fee">₹<?php echo (int) $b[1]; ?></p><a href="<?php echo esc_url($join); ?>" target="_blank" rel="noopener"><?php echo eh_t('Join', 'जुड़ें'); ?><?php echo $arrow; ?></a></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="uh-sec uh-how">
  <div class="wrap uh-how-in">
    <div class="uh-how-img" data-rv><img src="<?php echo esc_url($ph($P['class'], 1100)); ?>" alt="" loading="lazy">
      <blockquote><?php echo eh_t('Your preparation, our responsibility.', 'तैयारी आपकी, जिम्मेदारी हमारी!'); ?></blockquote></div>
    <div class="uh-how-txt">
      <div data-rv>
        <p class="uh-kick"><?php echo eh_t('Mock tests and daily quiz', 'मॉक टेस्ट और रोज़ क्विज'); ?></p>
        <h2 class="uh-h2"><?php echo eh_t('Read the notes. Practise daily. <em>Test yourself.</em>', 'नोट्स पढ़िए। रोज़ अभ्यास कीजिए। <em>खुद को परखिए।</em>'); ?></h2>
      </div>
      <ol class="uh-steps">
        <li data-rv><b>01</b><div><h3><?php echo eh_t('Notes', 'नोट्स'); ?></h3><p><?php echo eh_t('Clear notes made from the syllabus and previous year papers, as printable PDFs.', 'सिलेबस और पिछले पेपर देखकर बने साफ़ नोट्स, प्रिंट होने वाली PDF में।'); ?></p></div></li>
        <li data-rv><b>02</b><div><h3><?php echo eh_t('Daily quiz', 'रोज़ क्विज'); ?></h3><p><?php echo eh_t('A topic-wise quiz inside your batch, so you practise what you read.', 'बैच के अंदर टॉपिक-वाइज क्विज, ताकि जो पढ़ा उसका अभ्यास हो।'); ?></p></div></li>
        <li data-rv><b>03</b><div><h3><?php echo eh_t('Mock tests', 'मॉक टेस्ट'); ?></h3><p><?php echo eh_t('Full-length and sectional tests on the exam pattern.', 'एग्जाम पैटर्न पर फुल लेंथ और सेक्शनल टेस्ट।'); ?></p></div></li>
        <li data-rv><b>04</b><div><h3><?php echo eh_t('Typing', 'टाइपिंग'); ?></h3><p><?php echo eh_t('For typing posts, practise free on the same screen you will see in the exam hall.', 'टाइपिंग वाली भर्तियों के लिए, एग्जाम हॉल जैसी स्क्रीन पर फ्री प्रैक्टिस।'); ?></p></div></li>
      </ol>
    </div>
  </div>
</section>

<section class="uh-type" id="typing">
  <img class="uh-type-bg" src="<?php echo esc_url($ph($P['keys'], 1600)); ?>" alt="" loading="lazy">
  <div class="wrap">
    <div class="uh-type-top">
      <div data-rv>
        <p class="uh-kick"><?php echo eh_t('Junior Assistant Live Typing', 'जूनियर असिस्टेंट लाइव टाइपिंग'); ?></p>
        <h2 class="uh-h2"><?php echo eh_t('The exam screen, <em>before the exam.</em>', 'एग्जाम वाली स्क्रीन, <em>एग्जाम से पहले।</em>'); ?></h2>
        <p class="uh-sub"><?php echo eh_t('English and Hindi (Krutidev, Mangal, Remington). Time, target and error rules are set for each exam. Your result comes the moment you submit.', 'English और हिंदी (Krutidev, Mangal, Remington)। हर एग्जाम का समय, टारगेट और गलती के नियम सेट हैं। सबमिट करते ही रिजल्ट।'); ?></p>
        <div class="uh-cta">
          <a class="uh-btn" href="<?php echo esc_url(home_url('/exams/')); ?>"><?php echo eh_t('All ' . count($exams) . ' exams', 'सभी ' . count($exams) . ' एग्जाम'); ?><?php echo $arrow; ?></a>
          <a class="uh-btn ghost" href="<?php echo esc_url(home_url('/demo-typing/')); ?>"><?php echo eh_t('Demo typing', 'डेमो टाइपिंग'); ?></a>
        </div>
      </div>
      <div class="uh-win" data-rv aria-hidden="true">
        <div class="uh-win-bar">UPSSSC Junior Assistant English Typing <span>09:42</span></div>
        <div class="uh-win-psg" id="uh-psg">The government has launched a new scheme for farmers in all districts of the state. Every office will keep a record of the work done each month.</div>
        <div class="uh-win-box"><span id="uh-typed"></span><b>|</b></div>
      </div>
    </div>

    <div class="uh-tlinks" data-rv>
      <a href="<?php echo esc_url(home_url('/live-typing/')); ?>"><b><?php echo eh_t('Live Typing', 'लाइव टाइपिंग'); ?></b><span><?php echo eh_t('One passage for everyone, every day. See your rank.', 'रोज़ सबके लिए एक पैसेज। अपनी रैंक देखें।'); ?></span><?php echo $arrow; ?></a>
      <a href="<?php echo esc_url(home_url('/exam-hall-typing/')); ?>"><b><?php echo eh_t('Exam Hall', 'एग्जाम हॉल'); ?></b><span><?php echo eh_t('A new passage at a fixed time, three times a day.', 'दिन में तीन बार, तय समय पर नया पैसेज।'); ?></span><?php echo $arrow; ?></a>
      <a href="<?php echo esc_url(home_url('/download-passages/')); ?>"><b><?php echo eh_t('Passage PDF', 'पैसेज PDF'); ?></b><span><?php echo eh_t('Download English and Hindi passages.', 'English और हिंदी पैसेज डाउनलोड करें।'); ?></span><?php echo $arrow; ?></a>
      <a href="<?php echo esc_url(home_url('/exam-based-result/')); ?>"><b><?php echo eh_t('Results', 'रिजल्ट'); ?></b><span><?php echo eh_t('Exam-wise results and ranking.', 'एग्जाम-वाइज रिजल्ट और रैंकिंग।'); ?></span><?php echo $arrow; ?></a>
    </div>

    <div class="uh-chips" data-rv>
      <?php foreach (['UPSSSC', 'SSC', 'High Court', 'UP Police', 'RRB NTPC', 'DSSSB', 'KVS', 'Bihar', 'Rajasthan', 'Delhi Police', 'AIIMS', 'MP'] as $k): ?>
        <a href="<?php echo esc_url(home_url('/exams/?q=' . rawurlencode($k))); ?>"><?php echo esc_html($k); ?></a>
      <?php endforeach; ?>
    </div>
    <div class="uh-egrid" data-rv>
      <?php foreach (array_slice($popular, 0, 6) as $e) echo eh_exam_card($e, $att[$e['slug']] ?? 0); ?>
    </div>

    <div class="uh-recent" data-rv>
      <h3><?php echo eh_t('Students practising now', 'अभी प्रैक्टिस कर रहे विद्यार्थी'); ?></h3>
      <div class="uh-tbl">
        <table class="eh-table" id="hx-recent">
          <thead><tr><th>#</th><th>Name</th><th>Exam</th><th>Gross</th><th>Net</th></tr></thead>
          <tbody>
          <?php if (!$recent): ?><tr><td colspan="5" class="muted">No results yet. Be the first to take a test.</td></tr><?php endif; ?>
          <?php foreach ($recent as $i => $r): $e = eh_exam($r['exam']); ?>
            <tr<?php echo $i >= 5 ? ' hidden' : ''; ?>><td><?php echo $i + 1; ?></td><td><?php echo esc_html(eh_display_name($r)); ?></td><td><?php echo esc_html($e ? $e['name'] : 'Demo Typing'); ?></td><td><?php echo (int) $r['gross']; ?></td><td><b><?php echo (int) $r['net']; ?></b></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (count($recent) > 5): ?>
        <div class="hx-pg"><button type="button" class="btn ghost" data-d="-1" disabled>Previous</button><button type="button" class="btn dark" data-d="1">Next</button><span>Page <b>1</b> of <?php echo (int) ceil(count($recent) / 5); ?></span></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="uh-sec" id="free">
  <div class="wrap">
    <div class="uh-head" data-rv>
      <p class="uh-kick"><?php echo eh_t('Free content', 'फ्री कंटेंट'); ?></p>
      <h2 class="uh-h2"><?php echo eh_t('Start today. <em>Pay nothing.</em>', 'आज ही शुरू कीजिए। <em>बिना किसी फीस के।</em>'); ?></h2>
    </div>
    <div class="uh-free">
      <a class="uh-f big" data-rv href="<?php echo esc_url(eh_opt('tg_channel')); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url($ph($P['books'], 1000)); ?>" alt="" loading="lazy"><div><p class="uh-tag">Telegram</p><h3><?php echo eh_t('Daily exam updates and free PDFs', 'रोज़ की एग्जाम अपडेट और फ्री PDF'); ?></h3><span><?php echo eh_t('Open the channel', 'चैनल खोलें'); ?><?php echo $arrow; ?></span></div></a>
      <a class="uh-f" data-rv href="<?php echo esc_url(eh_opt('youtube')); ?>" target="_blank" rel="noopener"><p class="uh-tag">YouTube</p><h3><?php echo eh_t('Videos and updates on XL Study', 'XL Study पर वीडियो और अपडेट'); ?></h3><span><?php echo eh_t('Watch', 'देखें'); ?><?php echo $arrow; ?></span></a>
      <a class="uh-f" data-rv href="https://t.me/JE_mech_2026" target="_blank" rel="noopener"><p class="uh-tag">UP JE Mechanical</p><h3><?php echo eh_t('Smart notes, short notes and a study planner', 'स्मार्ट नोट्स, शॉर्ट नोट्स और स्टडी प्लानर'); ?></h3><span><?php echo eh_t('Get the PDFs', 'PDF लें'); ?><?php echo $arrow; ?></span></a>
      <a class="uh-f" data-rv href="<?php echo esc_url(home_url('/exams/')); ?>"><p class="uh-tag"><?php echo eh_t('Typing', 'टाइपिंग'); ?></p><h3><?php echo eh_t(count($exams) . ' exam typing tests, always free', count($exams) . ' एग्जाम टाइपिंग टेस्ट, हमेशा फ्री'); ?></h3><span><?php echo eh_t('Start typing', 'टाइपिंग शुरू करें'); ?><?php echo $arrow; ?></span></a>
      <a class="uh-f" data-rv href="<?php echo esc_url(home_url('/blog/')); ?>"><p class="uh-tag"><?php echo eh_t('Blog', 'ब्लॉग'); ?></p><h3><?php echo eh_t('Typing rules and tips for each exam', 'हर एग्जाम के टाइपिंग नियम और टिप्स'); ?></h3><span><?php echo eh_t('Read', 'पढ़ें'); ?><?php echo $arrow; ?></span></a>
    </div>
  </div>
</section>

<section class="uh-join" id="join">
  <div class="wrap">
    <h2 class="uh-h2" data-rv><?php echo eh_t('Stay with us. <em>Every update, first.</em>', 'हमसे जुड़े रहिए। <em>हर अपडेट, सबसे पहले।</em>'); ?></h2>
    <div class="uh-jgrid">
      <a data-rv href="<?php echo esc_url(eh_opt('tg_channel')); ?>" target="_blank" rel="noopener"><small>Telegram</small><b><?php echo eh_t('Join the channel', 'चैनल से जुड़ें'); ?></b><span>@upssscalertt</span><?php echo $arrow; ?></a>
      <a data-rv href="<?php echo esc_url(eh_opt('tg_group')); ?>" target="_blank" rel="noopener"><small>Telegram</small><b><?php echo eh_t('Join the group', 'ग्रुप से जुड़ें'); ?></b><span>@upssscalert</span><?php echo $arrow; ?></a>
      <a data-rv href="<?php echo esc_url($join); ?>" target="_blank" rel="noopener"><small>Telegram</small><b><?php echo eh_t('Join a batch', 'बैच में जुड़ें'); ?></b><span>@joinvijeta2025</span><?php echo $arrow; ?></a>
      <a data-rv href="<?php echo esc_url(eh_opt('youtube')); ?>" target="_blank" rel="noopener"><small>YouTube</small><b><?php echo eh_t('Subscribe', 'सब्सक्राइब करें'); ?></b><span>@xlstudy</span><?php echo $arrow; ?></a>
      <a data-rv href="<?php echo esc_url(eh_wa_url()); ?>" target="_blank" rel="noopener"><small>WhatsApp</small><b><?php echo eh_t('Message us', 'मैसेज करें'); ?></b><span>+<?php echo esc_html(preg_replace('/^(\d{2})(\d{5})(\d{5})$/', '$1 $2 $3', eh_opt('whatsapp'))); ?></span><?php echo $arrow; ?></a>
    </div>
  </div>
</section>

<section class="uh-sec uh-faq">
  <div class="wrap uh-faq-in">
    <div data-rv><p class="uh-kick">FAQ</p><h2 class="uh-h2"><?php echo eh_t('Questions <em>students ask.</em>', 'विद्यार्थी जो <em>सवाल पूछते हैं।</em>'); ?></h2></div>
    <div class="uh-acc" data-rv>
      <details open><summary><?php echo eh_t('How do I join a batch?', 'बैच में कैसे जुड़ें?'); ?></summary><p><?php echo eh_t('Message @joinvijeta2025 on Telegram or WhatsApp us on 9455015600. Tell us the batch name and we will add you.', 'टेलीग्राम पर @joinvijeta2025 को मैसेज करें या 9455015600 पर व्हाट्सऐप करें। बैच का नाम बताइए, हम आपको जोड़ देंगे।'); ?></p></details>
      <details><summary><?php echo eh_t('Is the typing practice really free?', 'क्या टाइपिंग प्रैक्टिस सच में फ्री है?'); ?></summary><p><?php echo eh_t('Yes. All typing tests, live typing and results are free.', 'हाँ। सभी टाइपिंग टेस्ट, लाइव टाइपिंग और रिजल्ट फ्री हैं।'); ?></p></details>
      <details><summary><?php echo eh_t('How do I type in Krutidev?', 'Krutidev में कैसे टाइप करें?'); ?></summary><p><?php echo eh_t('Choose "Hindi - Krutidev" before starting. Keep your keyboard in English; the text appears in Hindi as you type.', 'शुरू करने से पहले "Hindi - Krutidev" चुनें। कीबोर्ड English में रखें; टाइप करते ही टेक्स्ट हिंदी में आएगा।'); ?></p></details>
      <details><summary><?php echo eh_t('What is the difference between full and half mistakes?', 'फुल और हाफ मिस्टेक में क्या फर्क है?'); ?></summary><p><?php echo eh_t('A wrong, missing or extra word is a full mistake. Spacing, capitals and punctuation errors are half mistakes.', 'गलत, छूटा हुआ या ज़्यादा शब्द फुल मिस्टेक है। स्पेस, कैपिटल और विराम चिह्न की गलती हाफ मिस्टेक है।'); ?></p></details>
      <details><summary><?php echo eh_t('Can I practise typing on mobile?', 'क्या मोबाइल पर टाइपिंग प्रैक्टिस हो सकती है?'); ?></summary><p><?php echo eh_t('English tests work on mobile, but use a computer for real exam practice.', 'English टेस्ट मोबाइल पर चलते हैं, पर असली एग्जाम प्रैक्टिस के लिए कंप्यूटर इस्तेमाल करें।'); ?></p></details>
    </div>
  </div>
</section>

</div>

<aside class="uh-yt" id="uh-yt" data-list="<?php echo esc_attr(eh_opt('yt_uploads')); ?>" hidden>
  <div class="uh-yt-h"><a href="<?php echo esc_url(eh_opt('youtube')); ?>" target="_blank" rel="noopener"><i></i>XL Study · YouTube</a><button type="button" class="uh-yt-min" aria-label="Minimise">–</button><button type="button" class="uh-yt-x" aria-label="Close">✕</button></div>
  <div class="uh-yt-v"></div>
</aside>
<?php get_footer();
