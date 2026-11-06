<?php get_header(); $exams = eh_exams(); $att = eh_attempt_counts(); ?>
<section class="eh-page-head"><div class="wrap">
  <h1>All Typing Exams</h1><p class="muted"><?php echo count($exams); ?> परीक्षाओं का टाइपिंग पैटर्न — अपनी परीक्षा चुनें और टेस्ट शुरू करें।</p>
</div></section>
<section class="wrap eh-section">
  <div class="eh-filters">
    <input id="eh-exam-search" type="search" placeholder="परीक्षा खोजें… जैसे SSC, High Court, UPSSSC" autocomplete="off">
    <select id="eh-f-cat"><option value="">सभी कैटेगरी</option><?php foreach (['court', 'central', 'central-org', 'state'] as $c) echo '<option value="' . $c . '">' . esc_html(eh_cat_label($c)) . '</option>'; ?></select>
    <select id="eh-f-lang"><option value="">सभी भाषा</option><option value="en">English</option><option value="hi">Hindi</option><option value="mr">Marathi</option><option value="or">Odia</option></select>
    <span class="muted"><b id="eh-count"><?php echo count($exams); ?></b> exams</span>
  </div>
  <div class="eh-quick">
    <?php foreach (['AHC', 'AIIMS', 'BSF', 'Bombay', 'Delhi High Court', 'KVS', 'CPCT', 'NTPC', 'PHHC', 'RSSB', 'SCI', 'SSC CGL', 'SSC CHSL', 'UP Police', 'UPSSSC', 'DSSSB'] as $k) echo '<button type="button" data-q="' . esc_attr(strtolower($k)) . '">' . esc_html($k) . '</button>'; ?>
  </div>
  <div class="eh-grid"><?php foreach ($exams as $e) echo eh_exam_card($e, $att[$e['slug']] ?? 0); ?></div>
</section>
<?php get_footer();
