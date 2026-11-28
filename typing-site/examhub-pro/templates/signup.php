<?php $err = $GLOBALS['eh_auth_error'] ?? ''; $v = function ($k) { return esc_attr(wp_unslash($_POST[$k] ?? '')); }; $eh_mockauth = strpos(rawurldecode((string) ($_REQUEST['redirect_to'] ?? '')), 'super-tet-mock') !== false || strpos(rawurldecode((string) ($_REQUEST['redirect_to'] ?? '')), 'mock-test') !== false;
if ($eh_mockauth) add_filter('body_class', function ($c) { $c[] = 'eh-mockauth'; return $c; });
get_header(); ?>
<?php if ($eh_mockauth): ?><div class="eh-mhd"><a class="eh-mhd-b" href="<?php echo esc_url(home_url('/mock-test/')); ?>"><img src="<?php echo esc_url(EH_URI . '/assets/upsssc-alert-logo.svg'); ?>" alt="UPSSSC ALERT" width="42" height="42"><span><b>UPSSSC ALERT</b><small>Online Examination</small></span></a><span class="eh-mhd-x"><i>XL</i><b>XL Study</b></span></div><?php endif; ?>
<main class="eh-auth2 eh-au">
  <?php include __DIR__ . '/_auth-side.php'; ?>
  <div class="eh-auth2-card">
    <div class="eh-auth2-tabs"><a href="<?php echo esc_url(eh_login_page_url(isset($_REQUEST['redirect_to']) ? eh_safe_redirect_target() : '')); ?>">Log in</a><a class="on" href="<?php echo esc_url(add_query_arg(isset($_REQUEST['redirect_to']) ? ['redirect_to' => rawurlencode(eh_safe_redirect_target())] : [], eh_signup_page_url())); ?>">New user</a></div>
    <h1>Create your account</h1>
    <p class="eh-auth2-sub">Free, and it takes under a minute.</p>
    <?php if ($err): ?><div class="eh-alert"><?php echo esc_html($err); ?></div><?php endif; ?>
    <form method="post" class="eh-auth2-form">
      <?php wp_nonce_field('eh_auth', '_eh'); ?>
      <div class="eh-auth2-two">
        <div><label for="eh-fn">First name</label><input id="eh-fn" name="fn" required placeholder="e.g. Rahul" value="<?php echo $v('fn'); ?>"></div>
        <div><label for="eh-ln">Last name</label><input id="eh-ln" name="ln" placeholder="e.g. Kumar" value="<?php echo $v('ln'); ?>"></div>
      </div>
      <label for="eh-em">Email</label>
      <input id="eh-em" name="em" type="email" required autocomplete="email" placeholder="rahul@gmail.com" value="<?php echo $v('em'); ?>">
      <label for="eh-mo">Mobile number <small>optional</small></label>
      <input id="eh-mo" name="mo" type="tel" inputmode="numeric" maxlength="10" placeholder="10-digit number" value="<?php echo $v('mo'); ?>">
      <label for="eh-pw">Password <small>min. 6 characters</small></label>
      <span class="eh-pw2"><input id="eh-pw" name="pw" type="password" minlength="6" required autocomplete="new-password" placeholder="Create a password"><button type="button" onclick="var i=document.getElementById('eh-pw');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'Show':'Hide'">Show</button></span>
      <label class="eh-auth2-chk"><input type="checkbox" name="agree" value="1" required> I agree to the <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>" target="_blank">Terms</a> and <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" target="_blank">Privacy Policy</a></label>
      <button class="eh-auth2-btn" type="submit">Create free account</button>
    </form>
    <?php $g = eh_google_button(); if ($g): ?><div class="eh-auth2-or"><span>or</span></div><div class="eh-auth2-google"><?php echo $g; ?></div><?php endif; ?>
    <p class="eh-auth2-switch">Already have an account? <a href="<?php echo esc_url(eh_login_page_url(isset($_REQUEST['redirect_to']) ? eh_safe_redirect_target() : '')); ?>">Log in</a></p>
  </div>
</main>
<?php get_footer();
