<?php $err = $GLOBALS['eh_auth_error'] ?? ''; $eh_mockauth = strpos(rawurldecode((string) ($_REQUEST['redirect_to'] ?? '')), 'super-tet-mock') !== false;
if ($eh_mockauth) add_filter('body_class', function ($c) { $c[] = 'eh-mockauth'; return $c; });
get_header(); ?>
<?php if ($eh_mockauth): ?><div class="eh-mhd"><a class="eh-mhd-b" href="<?php echo esc_url(home_url('/super-tet-mock/')); ?>"><img src="<?php echo esc_url(EH_URI . '/assets/upsssc-alert-logo.svg'); ?>" alt="UPSSSC ALERT" width="42" height="42"><span><b>UPSSSC ALERT</b><small>Online Examination</small></span></a><span class="eh-mhd-x"><i>XL</i><b>XL Study</b></span></div><?php endif; ?>
<main class="eh-auth2 eh-au">
  <?php include __DIR__ . '/_auth-side.php'; ?>
  <div class="eh-auth2-card">
    <div class="eh-auth2-tabs"><a class="on" href="<?php echo esc_url(eh_login_page_url(isset($_REQUEST['redirect_to']) ? eh_safe_redirect_target() : '')); ?>">Log in</a><a href="<?php echo esc_url(add_query_arg(isset($_REQUEST['redirect_to']) ? ['redirect_to' => rawurlencode(eh_safe_redirect_target())] : [], eh_signup_page_url())); ?>">New user</a></div>
    <h1>Welcome back</h1>
    <p class="eh-auth2-sub">Log in with your email or mobile number.</p>
    <?php if ($err): ?><div class="eh-alert"><?php echo esc_html($err); ?></div><?php endif; ?>
    <form method="post" class="eh-auth2-form" novalidate>
      <?php wp_nonce_field('eh_auth', '_eh'); ?>
      <input type="hidden" name="redirect_to" value="<?php echo esc_attr(eh_safe_redirect_target()); ?>">
      <label for="eh-id">Email or mobile number</label>
      <input id="eh-id" name="id" type="text" required autocomplete="username" placeholder="e.g. rahul@gmail.com or 9876543210" value="<?php echo esc_attr(wp_unslash($_POST['id'] ?? '')); ?>">
      <label for="eh-pw">Password</label>
      <span class="eh-pw2"><input id="eh-pw" name="pw" type="password" required autocomplete="current-password" placeholder="Enter your password"><button type="button" aria-label="Show password" onclick="var i=document.getElementById('eh-pw');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'Show':'Hide'">Show</button></span>
      <a class="eh-auth2-forgot" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot password?</a>
      <button class="eh-auth2-btn" type="submit">Login</button>
    </form>
    <?php $g = eh_google_button(); if ($g): ?><div class="eh-auth2-or"><span>or</span></div><div class="eh-auth2-google"><?php echo $g; ?></div><?php endif; ?>
    <p class="eh-auth2-switch">New here? <a href="<?php echo esc_url(add_query_arg(isset($_REQUEST['redirect_to']) ? ['redirect_to' => rawurlencode(eh_safe_redirect_target())] : [], eh_signup_page_url())); ?>">Create a free account</a></p>
  </div>
</main>
<?php get_footer();
