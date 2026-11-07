<?php $err = $GLOBALS['eh_auth_error'] ?? ''; get_header(); ?>
<main class="eh-auth2">
  <div class="eh-auth2-card">
    <div class="eh-auth2-tabs"><a class="on" href="<?php echo esc_url(eh_login_page_url()); ?>">Login</a><a href="<?php echo esc_url(eh_signup_page_url()); ?>">New User</a></div>
    <h1>Login</h1>
    <p class="eh-auth2-sub">Log in to keep your typing history and results.</p>
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
    <p class="eh-auth2-switch">New here? <a href="<?php echo esc_url(eh_signup_page_url()); ?>">New user? Sign up</a></p>
  </div>
  <ul class="eh-auth2-perks"><li>✔ 100% free, no plans</li><li>✔ Result and history for every test</li><li>✔ Your rank in Live Typing</li></ul>
</main>
<?php get_footer();
