<?php
$q = sanitize_text_field(wp_unslash($_GET['s2'] ?? ''));
$pg = max(1, (int) ($_GET['pg'] ?? 1));
$args = ['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 12, 'paged' => $pg, 'category_name' => 'typing-tips'];
if ($q) $args['s'] = $q;
$Q = new WP_Query($args);
get_header(); ?>
<section class="wrap eh-section eh-blog">
  <p class="eh-kicker center">Typing Tips</p>
  <h1 class="center">एग्ज़ाम के हिसाब से Typing Rules और Tips</h1>
  <form class="eh-blog-search" method="get"><input name="s2" value="<?php echo esc_attr($q); ?>" placeholder="<?php echo esc_attr((int) $Q->found_posts); ?> पोस्ट में खोजें — जैसे SSC, High Court, UPSSSC"><button class="btn dark">खोजें</button></form>
  <div class="eh-blog-grid">
    <?php if (!$Q->have_posts()): ?><p class="muted center">कोई पोस्ट नहीं मिली।</p><?php endif; ?>
    <?php while ($Q->have_posts()): $Q->the_post(); ?>
      <a class="eh-bcard" href="<?php the_permalink(); ?>">
        <span class="eh-bcard-img"><?php if (has_post_thumbnail()) the_post_thumbnail('medium_large'); else echo '<b>' . esc_html(strtok(get_the_title(), ':')) . '</b>'; ?></span>
        <span class="eh-bcard-b"><small><?php echo esc_html(get_the_date('d M Y')); ?></small><b><?php the_title(); ?></b><em><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></em></span>
      </a>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <?php if ($Q->max_num_pages > 1): ?><div class="eh-pager"><?php for ($k = 1; $k <= $Q->max_num_pages; $k++) echo '<a class="' . ($k === $pg ? 'on' : '') . '" href="' . esc_url(add_query_arg('pg', $k)) . '">' . $k . '</a>'; ?></div><?php endif; ?>
</section>
<?php get_footer();
