<?php get_header(); while (have_posts()): the_post(); ?>
<article class="wrap eh-section eh-article">
  <p class="muted small"><?php echo get_the_date(); ?></p><h1><?php the_title(); ?></h1>
  <div class="eh-content"><?php the_content(); ?></div>
  <?php echo eh_tg_buttons(); ?>
</article>
<?php endwhile; get_footer();
