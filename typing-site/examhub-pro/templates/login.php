<?php include __DIR__ . '/_auth-top.php'; $err = $GLOBALS['eh_auth_error'] ?? ''; ?>
<main class="eh-auth-wrap">
  <div class="eh-auth-card">
    <h1>Welcome back 👋</h1>
    <p class="muted">Login करें और अपनी टाइपिंग प्रैक्टिस जारी रखें</p>
    <?php if ($err): ?><div class="eh-alert"><?php echo esc_html($err); ?></div><?php endif; ?>
    <?php echo eh_google_button(); ?>
    <form method="post" class="eh-auth-form">
      <?php wp_nonce_field('eh_auth', '_eh'); ?>
      <input type="hidden" name="redirect_to" value="<?php echo esc_attr(eh_safe_redirect_target()); ?>">
      <label>Email या Mobile<input name="id" type="text" required autocomplete="username" value="<?php echo esc_attr(wp_unslash($_POST['id'] ?? '')); ?>"></label>
      <label>Password<span class="eh-pw"><input name="pw" type="password" required autocomplete="current-password"><button type="button" onclick="var i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'">👁</button></span></label>
      <div class="eh-auth-row"><span></span><a href="<?php echo esc_url(wp_lostpassword_url()); ?>">पासवर्ड भूल गए?</a></div>
      <button class="btn dark lg block" type="submit">LOGIN</button>
    </form>
    <p class="eh-auth-switch">नया अकाउंट? <a href="<?php echo esc_url(eh_signup_page_url()); ?>">New User — Sign up</a></p>
  </div>
  <div class="eh-auth-art"><img src="<?php echo esc_url(EH_URI . '/assets/hero.svg'); ?>" alt="" width="460" height="394"></div>
</main>
<?php wp_footer(); ?></body></html>
