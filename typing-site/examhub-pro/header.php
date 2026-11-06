<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php $tick = eh_ticker_items(); if ($tick): ?>
<div class="eh-ticker" data-n="<?php echo count($tick); ?>">
  <div class="wrap eh-ticker-in">
    <button type="button" class="tk-prev" aria-label="पिछला">‹</button><button type="button" class="tk-next" aria-label="अगला">›</button>
    <div class="tk-items"><?php foreach ($tick as $i => $t): ?><a class="tk<?php echo $i ? '' : ' on'; ?>" href="<?php echo esc_url($t[1]); ?>"><span class="tk-n"><?php echo ($i + 1) . '/' . count($tick); ?>.</span> <?php echo esc_html($t[0]); ?> <b><?php echo esc_html($t[2]); ?></b></a><?php endforeach; ?></div>
  </div>
</div>
<?php endif; ?>
<header class="eh-header">
  <div class="wrap eh-header-in">
    <a class="eh-logo" href="<?php echo esc_url(home_url('/')); ?>">
      <span class="eh-logo-mark">EH</span><span class="eh-logo-txt"><?php echo esc_html(eh_opt('brand')); ?></span>
    </a>
    <button class="eh-burger" aria-label="Menu" onclick="document.body.classList.toggle('nav-open')">☰</button>
    <nav class="eh-nav">
      <?php if (has_nav_menu('primary')) wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'eh-menu']); else eh_default_menu(); ?>
      <div class="eh-auth">
        <?php if (is_user_logged_in()): $u = wp_get_current_user(); ?>
          <button type="button" class="eh-avatar-btn" data-drawer="eh-user-drawer" aria-label="Profile menu"><?php echo esc_html(eh_initials($u->display_name)); ?></button>
        <?php else: ?>
          <a class="btn ghost sm" href="<?php echo esc_url(eh_login_page_url()); ?>">Login</a>
          <a class="btn dark sm" href="<?php echo esc_url(eh_signup_page_url()); ?>">New User</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>
<?php if (is_user_logged_in()): $u = wp_get_current_user(); ?>
<aside id="eh-user-drawer" class="eh-drawer" hidden>
  <div class="eh-drawer-in">
    <div class="eh-drawer-h"><b><?php echo esc_html(eh_opt('brand')); ?></b><button type="button" data-close aria-label="Close">✕</button></div>
    <div class="eh-drawer-card">
      <span class="eh-av-big"><?php echo esc_html(eh_initials($u->display_name)); ?></span>
      <p>नमस्ते, <b><?php echo esc_html($u->display_name); ?></b></p>
      <div class="eh-drawer-btns"><a class="btn ghost sm" href="<?php echo esc_url(admin_url('profile.php')); ?>">✎ Edit Profile</a><a class="btn sm eh-out" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">⎋ Logout</a></div>
      <ul class="eh-drawer-links"><?php foreach (eh_member_links() as $l) echo '<li><a href="' . esc_url(home_url($l[0])) . '">' . esc_html($l[1]) . '</a></li>'; ?></ul>
      <p class="muted small">मदद चाहिए? Telegram पर पूछें</p>
      <?php echo eh_tg_buttons('inline'); ?>
    </div>
  </div>
</aside>
<?php endif; ?>
<main class="eh-main">
