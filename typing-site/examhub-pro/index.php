<?php get_header(); ?>
<section class="eh-page-head slim"><div class="wrap"><h1><?php
if (is_category()) single_cat_title(); elseif (is_search()) echo 'Search: ' . esc_html(get_search_query()); elseif (is_archive()) the_archive_title(); else echo 'Typing Tips & Updates'; ?></h1></div></section>
<section class="wrap eh-section"><div class="eh-posts">
<?php if (have_posts()): while (have_posts()): the_post(); ?>
  <article class="eh-card post"><a href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) the_post_thumbnail('medium_large'); ?><h3><?php the_title(); ?></h3></a><p class="muted small"><?php echo get_the_date(); ?></p><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p></article>
<?php endwhile; else: ?><p class="muted">अभी यहाँ कोई पोस्ट नहीं है।</p><?php endif; ?>
</div><div class="eh-pager"><?php the_posts_pagination(); ?></div></section>
<?php get_footer();
