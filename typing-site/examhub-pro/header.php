<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php $notice = eh_opt('notice'); if ($notice): ?>
<div class="eh-notice"><a href="<?php echo esc_url(home_url('/live-typing/')); ?>"><?php echo esc_html($notice); ?> →</a></div>
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
          <a class="btn ghost sm" href="<?php echo esc_url(home_url('/my-dashboard/')); ?>">👤 <?php echo esc_html($u->display_name); ?></a>
          <a class="btn dark sm" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
        <?php else: ?>
          <a class="btn ghost sm" href="<?php echo esc_url(eh_login_page_url()); ?>">Login</a>
          <a class="btn dark sm" href="<?php echo esc_url(eh_signup_page_url()); ?>">New User</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>
<main class="eh-main">
