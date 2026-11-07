<?php get_header();
$exams = eh_exams(); $att = eh_attempt_counts(); $total = array_sum($att);
$popular = $exams; usort($popular, function ($a, $b) use ($att) { return ($att[$b['slug']] ?? 0) <=> ($att[$a['slug']] ?? 0); });
$recent = eh_recent(100);
$np = count(eh_passages()); ?>

<section class="hx-hero">
  <div class="wrap hx-hero-in">
    <div class="hx-hero-txt">
      <h1>Free typing tests for every government exam.</h1>
      <p>Practice English and Hindi (Krutidev, Mangal, Remington) on the same screen you will see in the exam hall. Time, target and error rules are set for each exam. Your result comes the moment you submit.</p>
      <a class="hx-btn3d" href="<?php echo esc_url(home_url('/exams/')); ?>">Explore all exams <span>⟶</span></a>
      <a class="hx-link" href="<?php echo esc_url(home_url('/demo-typing/')); ?>">Demo Typing</a>
    </div>
    <div class="hx-hero-art" aria-hidden="true">
      <div class="hx-win">
        <div class="hx-win-bar">SSC CHSL English Typing <span>09:42</span></div>
        <div class="hx-win-psg"><i class="ok">The government has</i> <i class="cur">launched</i> a new scheme for farmers in all districts of the state...</div>
        <div class="hx-win-box">The government has launc<b>|</b></div>
      </div>
      <div class="hx-badge b1"><b><?php echo count($exams); ?>+</b><span>Exams</span></div>
      <div class="hx-badge b2"><b><?php echo number_format_i18n($np); ?>+</b><span>Passages</span></div>
      <div class="hx-badge b3"><b><?php echo $total >= 100 ? number_format_i18n($total) : '100%'; ?></b><span><?php echo $total >= 100 ? 'Tests taken' : 'Free'; ?></span></div>
    </div>
  </div>
</section>

<section class="hx-dark">
  <div class="wrap hx-feat">
    <div><span class="hx-ic"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg></span>
      <h3>Exam-pattern passages</h3><p>English and Hindi passages written the way real exams set them.</p></div>
    <div><span class="hx-ic"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg></span>
      <h3>Daily Live Typing</h3><p>One new passage every day for everyone. See where you rank.</p></div>
    <div><span class="hx-ic"><svg viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4.5 2 7.5L12 16.5 5.5 21l2-7.5L2 9h7z"/></svg></span>
      <h3>Rule-based checking</h3><p>Full mistakes, half mistakes and key depressions counted as per exam rules.</p></div>
  </div>
</section>

<section class="hx-exams">
  <div class="wrap">
    <p class="hx-kick">Our exams</p>
    <h2 class="hx-h2 c">Pick your exam and start typing</h2>
    <div class="hx-chips">
      <?php foreach (['SSC', 'High Court', 'UPSSSC', 'DSSSB', 'KVS', 'RRB NTPC', 'UP Police', 'Bihar', 'Rajasthan', 'Delhi Police', 'AIIMS', 'MP'] as $k): ?>
        <a href="<?php echo esc_url(home_url('/exams/?q=' . rawurlencode($k))); ?>"><?php echo esc_html($k); ?></a>
      <?php endforeach; ?>
    </div>
    <div class="hx-grid">
      <?php foreach (array_slice($popular, 0, 8) as $e) echo eh_exam_card($e, $att[$e['slug']] ?? 0); ?>
      <a class="hx-more" href="<?php echo esc_url(home_url('/exams/')); ?>"><b>Explore</b><span>All <?php echo count($exams); ?> exams</span><i>⟶</i></a>
    </div>
  </div>
</section>

<section class="wrap hx-sec hx-perf">
  <p class="hx-kick">Students practising now</p>
  <h2 class="hx-h2">Check your typing performance.</h2>
  <div class="hx-acc">
    <details open><summary>Recent tests</summary><p>The latest tests taken on the site. Practise daily and compare your speed with others.</p></details>
    <details><summary>How errors are counted</summary><p>A wrong, missing or extra word is a full mistake. Spacing, capital letter or punctuation slips are half mistakes. Error % = (full + half ÷ 2) ÷ words typed × 100.</p></details>
    <details><summary>Performance dashboard</summary><p>After every test you get gross speed, net speed, accuracy, key depressions, backspace count and a word-by-word comparison with the original passage.</p></details>
  </div>
  <a class="btn dark" href="<?php echo esc_url(home_url('/exam-based-result/')); ?>">Exam Based Typing Result</a>
  <div class="hx-tbl">
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
</section>

<section class="wrap hx-sec">
  <p class="hx-kick">Why Exam Hub</p>
  <h2 class="hx-h2">What makes us useful.</h2>
  <div class="hx-acc soft">
    <details open><summary>Free, fully</summary><p>No fee, no plan, no locked tests. Every exam, passage and result is free.</p></details>
    <details><summary>New passages every day</summary><p>Six new passages are added every day, plus a daily live passage for everyone.</p></details>
    <details><summary>One site, all exams</summary><p>SSC, High Courts, railway, state boards and more. One login keeps all your results.</p></details>
  </div>
  <div class="hx-cards">
    <div class="hx-c y"><h3>Exam interface</h3><p>Each exam opens in its own window size, font and layout, just like the real test.</p></div>
    <div class="hx-c p"><h3>Error formulas</h3><p>Half mistakes, full mistakes, backspace and key depressions are calculated as per each exam.</p></div>
    <div class="hx-c b"><h3>Feedback</h3><p>Found a problem or need help? Message us on Telegram.</p></div>
  </div>
</section>

<section class="wrap hx-sec">
  <h2 class="hx-h2">FAQs</h2>
  <div class="hx-acc soft">
    <details><summary>Is it really free?</summary><p>Yes. All tests, live typing and results are free.</p></details>
    <details><summary>How do I type in Krutidev?</summary><p>Choose "Hindi - Krutidev" before starting. Keep your keyboard in English; the text appears in Hindi as you type.</p></details>
    <details><summary>What is the difference between full and half mistakes?</summary><p>A wrong, missing or extra word is a full mistake. Spacing, capitals and punctuation errors are half mistakes.</p></details>
    <details><summary>Can I practise on mobile?</summary><p>English tests work on mobile, but use a computer for real exam practice.</p></details>
  </div>
</section>

<section class="wrap hx-cta">
  <h2>Any typing doubt in mind?<br>Let’s connect.</h2>
  <?php echo eh_tg_buttons(); ?>
</section>

<script>(function(){var t=document.getElementById('hx-recent'),pg=document.querySelector('.hx-pg');if(!t||!pg)return;var rows=[].slice.call(t.tBodies[0].rows),p=0,n=Math.ceil(rows.length/5),b=pg.querySelectorAll('button');
pg.addEventListener('click',function(e){var d=e.target.getAttribute('data-d');if(!d)return;p=Math.max(0,Math.min(n-1,p+(+d)));rows.forEach(function(r,i){r.hidden=Math.floor(i/5)!==p;});pg.querySelector('b').textContent=p+1;b[0].disabled=p===0;b[1].disabled=p===n-1;});})();</script>
<?php get_footer();
