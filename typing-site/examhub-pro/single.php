<?php get_header(); while (have_posts()): the_post(); $recent = get_posts(['numberposts' => 6, 'post__not_in' => [get_the_ID()], 'category_name' => 'typing-tips']); ?>
<div class="wrap eh-section eh-single">
  <article class="eh-article">
    <p class="muted small"><?php echo esc_html(get_the_date('d F, Y')); ?> · <?php echo esc_html(eh_opt('brand')); ?></p>
    <h1><?php the_title(); ?></h1>
    <?php if (has_post_thumbnail()) { echo '<div class="eh-feat-img">'; the_post_thumbnail('large'); echo '</div>'; } ?>
    <div class="eh-content"><?php the_content(); ?></div>
    <?php echo eh_tg_buttons(); ?>
  </article>
  <aside class="eh-side">
    <div class="eh-panel"><h3>हाल की पोस्ट</h3><ul class="eh-side-list"><?php foreach ($recent as $r) echo '<li><a href="' . esc_url(get_permalink($r)) . '">' . esc_html(get_the_title($r)) . '</a><small>' . esc_html(get_the_date('d M Y', $r)) . '</small></li>'; ?></ul></div>
    <div class="eh-panel eh-side-cta"><h3>फ्री टाइपिंग टेस्ट</h3><p class="muted small">129+ एग्ज़ाम, असली एग्ज़ाम जैसी स्क्रीन।</p><a class="btn dark block" href="<?php echo esc_url(home_url('/exams/')); ?>">All Exams →</a></div>
  </aside>
</div>
<?php endwhile; get_footer();
