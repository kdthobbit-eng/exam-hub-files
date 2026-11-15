<?php get_header();
$slug = get_query_var('eh_slug');
if ($slug === 'demo') {
    $l = (isset($_GET['lang']) && $_GET['lang'] === 'hi') ? 'hi' : 'en';
    $eh_exam = ['slug' => 'demo', 'name' => $l === 'hi' ? 'Demo Hindi Typing' : 'Demo English Typing', 'lang' => $l, 'unit' => 'words', 'target' => $l === 'hi' ? 250 : 300, 'minutes' => 5, 'cat' => 'other', 'allowed' => (float) eh_opt('allowed_error'), 'retype' => false];
} else { $eh_exam = eh_exam($slug); if (in_array($eh_exam['lang'], ['mr', 'or'], true)) $eh_exam['lang'] = 'en'; }
$eh_cfg = [];
/* 3284 Full Exam: English first, then Hindi on its own (?full=1 English, ?full=2 Hindi, &fs = set number, &kb = Hindi keyboard) */
$eh_full = (int) ($_GET['full'] ?? 0);
if (in_array($eh_full, [1, 2], true) && $slug === ($eh_full === 1 ? 'upsssc-assistants-english-typing-test' : 'upsssc-assistants-hindi-typing-test')) {
    $fl = $eh_full === 1 ? 'en' : 'hi';
    $fp = array_values(array_filter(eh_passages($fl), function ($p) { return ($p['day'] ?? '') === 'full3284'; }));
    if ($fp) {
        $fs = max(1, min(count($fp), (int) ($_GET['fs'] ?? 1)));
        $seq = array_merge(array_slice($fp, $fs - 1), array_slice($fp, 0, $fs - 1));
        $fkb = in_array($_GET['kb'] ?? '', ['krutidev', 'inscript'], true) ? $_GET['kb'] : 'krutidev';
        $eh_exam['name'] = 'UPSSSC 3284 Full Exam · ' . ($fl === 'en' ? 'Part 1 English' : 'Part 2 Hindi');
        $eh_cfg = ['fixedPassage' => $seq[0]['id'], 'passages' => $seq, 'lockTime' => true, 'full' => $eh_full, 'fullSet' => $fs, 'fullKb' => $fkb,
            'fullCount' => count($fp), 'fullFirst' => home_url('/typing/upsssc-assistants-english-typing-test/'), 'fullNext' => add_query_arg(['full' => 2, 'fs' => $fs, 'kb' => $fkb], home_url('/typing/upsssc-assistants-hindi-typing-test/'))];
        if ($eh_full === 2) $eh_cfg['layout'] = $fkb;
    }
}
?>
<section class="eh-page-head slim"><div class="wrap">
  <h1><?php echo esc_html($eh_exam['name']); ?><?php echo ($slug === 'demo' || stripos($eh_exam['name'], 'test') !== false) ? '' : ' — Typing Test'; ?></h1>
  <?php if ($slug === 'demo'): ?><p class="eh-switch"><a class="<?php echo $eh_exam['lang'] === 'en' ? 'on' : ''; ?>" href="<?php echo esc_url(home_url('/demo-typing/')); ?>">English</a><a class="<?php echo $eh_exam['lang'] === 'hi' ? 'on' : ''; ?>" href="<?php echo esc_url(home_url('/demo-typing/?lang=hi')); ?>">Hindi</a></p><?php endif; ?>
</div></section>
<section class="wrap eh-section"><?php include __DIR__ . '/_test-ui.php'; ?></section>
<?php get_footer();
