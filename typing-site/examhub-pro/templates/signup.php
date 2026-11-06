<?php include __DIR__ . '/_auth-top.php'; $err = $GLOBALS['eh_auth_error'] ?? ''; $v = function ($k) { return esc_attr(wp_unslash($_POST[$k] ?? '')); }; ?>
<main class="eh-auth-wrap">
  <div class="eh-auth-card">
    <h1>अपना Free अकाउंट बनाएं</h1>
    <p class="muted">एक मिनट से कम लगेगा · सारे रिज़ल्ट सेव रहेंगे</p>
    <?php if ($err): ?><div class="eh-alert"><?php echo esc_html($err); ?></div><?php endif; ?>
    <?php echo eh_google_button(); ?>
    <form method="post" class="eh-auth-form">
      <?php wp_nonce_field('eh_auth', '_eh'); ?>
      <div class="eh-two-in"><label>First Name<input name="fn" required value="<?php echo $v('fn'); ?>"></label><label>Last Name<input name="ln" value="<?php echo $v('ln'); ?>"></label></div>
      <label>Email<input name="em" type="email" required autocomplete="email" value="<?php echo $v('em'); ?>"></label>
      <label>Mobile <span class="muted small">(optional)</span><input name="mo" type="tel" inputmode="numeric" maxlength="10" placeholder="10 अंक" value="<?php echo $v('mo'); ?>"></label>
      <label>Password<span class="eh-pw"><input name="pw" type="password" minlength="6" required autocomplete="new-password"><button type="button" onclick="var i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'">👁</button></span></label>
      <label class="chk"><input type="checkbox" name="agree" value="1" required> मैं <a href="<?php echo esc_url(home_url('/terms/')); ?>" target="_blank">Terms</a> और <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" target="_blank">Privacy Policy</a> से सहमत हूँ</label>
      <button class="btn dark lg block" type="submit">SIGN UP — FREE</button>
    </form>
    <p class="eh-auth-switch">पहले से अकाउंट है? <a href="<?php echo esc_url(eh_login_page_url()); ?>">Login करें</a></p>
  </div>
  <div class="eh-auth-art"><img src="<?php echo esc_url(EH_URI . '/assets/hero.svg'); ?>" alt="" width="460" height="394"></div>
</main>
<?php wp_footer(); ?></body></html>
