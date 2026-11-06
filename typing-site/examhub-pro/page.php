<?php get_header(); while (have_posts()): the_post(); ?>
<article class="wrap eh-section eh-article"><h1><?php the_title(); ?></h1><div class="eh-content"><?php the_content(); ?></div></article>
<?php endwhile; get_footer();
