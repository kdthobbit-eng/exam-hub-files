<?php
$slug = get_query_var('eh_slug'); $I = eh_info_pages(); $pg = $I[$slug] ?? null;
if (!$pg) { global $wp_query; $wp_query->set_404(); status_header(404); include get_404_template(); exit; }
get_header(); ?>
<section class="eh-page-head slim"><div class="wrap"><h1><?php echo esc_html($pg['title']); ?></h1></div></section>
<section class="wrap eh-section eh-info">
<?php if ($slug === 'faqs'): $F = eh_faqs(); ?>
  <div class="eh-faq-grid">
    <div class="eh-faq-side"><p class="eh-kicker">सवाल और जवाब</p><h2>आपके सवाल, हमारे जवाब</h2><p class="muted">यहां जवाब नहीं मिला? Telegram पर सीधे पूछ लीजिए।</p><?php echo eh_tg_buttons('inline'); ?></div>
    <div class="eh-faq-list"><?php foreach ($F as $i => $q): ?><details <?php echo $i === 0 ? 'open' : ''; ?>><summary><?php echo esc_html($q[0]); ?></summary><p><?php echo esc_html($q[1]); ?></p></details><?php endforeach; ?></div>
  </div>
  <script type="application/ld+json"><?php echo wp_json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(function ($q) { return ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]]; }, $F)], JSON_UNESCAPED_UNICODE); ?></script>
<?php else: ?>
  <article class="eh-prose"><?php echo wp_kses_post($pg['html']); ?><p class="muted small">आखिरी बदलाव: <?php echo esc_html(wp_date('d M Y', filemtime(EH_DIR . '/inc/info.php'))); ?></p></article>
<?php endif; ?>
</section>
<?php get_footer();
