<?php get_header();
$slug = get_query_var('eh_slug');
if ($slug === 'demo') {
    $l = (isset($_GET['lang']) && $_GET['lang'] === 'hi') ? 'hi' : 'en';
    $eh_exam = ['slug' => 'demo', 'name' => $l === 'hi' ? 'Demo Hindi Typing' : 'Demo English Typing', 'lang' => $l, 'unit' => 'words', 'target' => $l === 'hi' ? 250 : 300, 'minutes' => 5, 'cat' => 'other', 'allowed' => (float) eh_opt('allowed_error'), 'retype' => false];
} else { $eh_exam = eh_exam($slug); if (in_array($eh_exam['lang'], ['mr', 'or'], true)) $eh_exam['lang'] = 'en'; }
$eh_cfg = [];
?>
<section class="eh-page-head slim"><div class="wrap">
  <h1><?php echo esc_html($eh_exam['name']); ?><?php echo $slug === 'demo' ? '' : ' — Typing Test'; ?></h1>
  <?php if ($slug === 'demo'): ?><p class="eh-switch"><a class="<?php echo $eh_exam['lang'] === 'en' ? 'on' : ''; ?>" href="<?php echo esc_url(home_url('/demo-typing/')); ?>">English</a><a class="<?php echo $eh_exam['lang'] === 'hi' ? 'on' : ''; ?>" href="<?php echo esc_url(home_url('/demo-typing/?lang=hi')); ?>">Hindi</a></p><?php endif; ?>
</div></section>
<section class="wrap eh-section"><?php include __DIR__ . '/_test-ui.php'; ?></section>
<?php get_footer();
