<?php
$exams = eh_exams();
$sel = sanitize_title($_GET['exam'] ?? ($_COOKIE['eh_hall_exam'] ?? ''));
$ex = eh_exam($sel) ?: (eh_exam('upsssc-assistants-hindi-typing-test') ?: $exams[0]);
$lang = $ex['lang'] === 'hi' ? 'hi' : 'en';
$slots = [['सुबह', 'Morning', 7 * 60, 8 * 60], ['दोपहर', 'Afternoon', 13 * 60 + 30, 14 * 60 + 30], ['शाम', 'Evening', 18 * 60 + 30, 19 * 60 + 30]];
$now = (int) wp_date('G') * 60 + (int) wp_date('i');
$fmt = function ($m) { return gmdate('h:i A', $m * 60); };
get_header(); ?>
<?php echo eh_member_nav('hall'); ?>
<section class="wrap eh-section eh-hall">
  <h1 class="eh-hall-h">रोज़ यहां टाइप करें, बिल्कुल <span>Exam Hall</span> जैसा</h1>
  <p class="center muted">हर दिन तीन बार नया पैसेज आता है। तय समय के अंदर टेस्ट दीजिए — जैसे असली एग्ज़ाम में तय शिफ़्ट होती है।</p>
  <div class="eh-slots">
    <?php foreach ($slots as $k => $s):
      $p = eh_slot_passage($lang, $k);
      $state = $now < $s[2] ? 'soon' : ($now <= $s[3] ? 'live' : 'done'); ?>
      <div class="eh-slot <?php echo $state; ?>">
        <span class="eh-slot-name"><?php echo esc_html($s[1] . ' · ' . $s[0]); ?></span>
        <b><?php echo esc_html($fmt($s[2]) . ' – ' . $fmt($s[3])); ?></b>
        <?php if ($state === 'live' && $p): ?>
          <span class="pill ok">● अभी चल रहा है</span>
          <a class="btn dark" href="<?php echo esc_url(add_query_arg(['passage' => $p['id'], 'hall' => 1], home_url('/typing/' . $ex['slug'] . '/'))); ?>">टेस्ट शुरू करें →</a>
        <?php elseif ($state === 'soon'): ?>
          <span class="muted small" data-start="<?php echo (int) $s[2]; ?>">पैसेज <?php echo esc_html($fmt($s[2])); ?> बजे खुलेगा</span>
        <?php else: ?>
          <span class="muted small">समय खत्म</span>
          <?php if ($p): ?><a class="btn ghost sm" href="<?php echo esc_url(add_query_arg('passage', $p['id'], home_url('/typing/' . $ex['slug'] . '/'))); ?>">प्रैक्टिस के लिए खोलें</a><?php endif; ?>
        <?php endif; ?>
      </div>
      <?php if ($k < 2): ?><span class="eh-slot-arrow">›</span><?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="eh-hall-exam">
    <div><p class="muted small">टाइपिंग के लिए चुना गया एग्ज़ाम</p><h2><?php echo esc_html($ex['name']); ?></h2>
      <p class="eh-rules"><span>🎯 <?php echo esc_html(eh_target_label($ex)); ?></span><span>⏱ <?php echo (int) $ex['minutes']; ?> min</span><span>❗ <?php echo esc_html($ex['allowed']); ?>% error</span></p></div>
    <form method="get" class="eh-hall-pick"><label>दूसरा एग्ज़ाम चुनें →<select name="exam" onchange="document.cookie='eh_hall_exam='+this.value+';path=/;max-age=31536000';this.form.submit()">
      <?php foreach ($exams as $e) echo '<option value="' . esc_attr($e['slug']) . '"' . selected($e['slug'], $ex['slug'], false) . '>' . esc_html($e['name']) . '</option>'; ?>
    </select></label></form>
  </div>
</section>
<?php get_footer();
