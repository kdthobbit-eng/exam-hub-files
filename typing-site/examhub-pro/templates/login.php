<?php $err = $GLOBALS['eh_auth_error'] ?? ''; get_header(); ?>
<main class="eh-auth2">
  <div class="eh-auth2-card">
    <div class="eh-auth2-tabs"><a class="on" href="<?php echo esc_url(eh_login_page_url()); ?>">Login</a><a href="<?php echo esc_url(eh_signup_page_url()); ?>">New User</a></div>
    <h1>Login</h1>
    <p class="eh-auth2-sub">अपने अकाउंट में login करें — आपकी Typing History और रिज़ल्ट सेव रहेंगे।</p>
    <?php if ($err): ?><div class="eh-alert"><?php echo esc_html($err); ?></div><?php endif; ?>
    <form method="post" class="eh-auth2-form" novalidate>
      <?php wp_nonce_field('eh_auth', '_eh'); ?>
      <input type="hidden" name="redirect_to" value="<?php echo esc_attr(eh_safe_redirect_target()); ?>">
      <label for="eh-id">Email या Mobile Number</label>
      <input id="eh-id" name="id" type="text" required autocomplete="username" placeholder="जैसे: rahul@gmail.com या 9876543210" value="<?php echo esc_attr(wp_unslash($_POST['id'] ?? '')); ?>">
      <label for="eh-pw">Password</label>
      <span class="eh-pw2"><input id="eh-pw" name="pw" type="password" required autocomplete="current-password" placeholder="अपना पासवर्ड डालें"><button type="button" aria-label="पासवर्ड दिखाएं" onclick="var i=document.getElementById('eh-pw');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'दिखाएं':'छुपाएं'">दिखाएं</button></span>
      <a class="eh-auth2-forgot" href="<?php echo esc_url(wp_lostpassword_url()); ?>">पासवर्ड भूल गए?</a>
      <button class="eh-auth2-btn" type="submit">Login</button>
    </form>
    <?php $g = eh_google_button(); if ($g): ?><div class="eh-auth2-or"><span>या</span></div><div class="eh-auth2-google"><?php echo $g; ?></div><?php endif; ?>
    <p class="eh-auth2-switch">नया अकाउंट बनाना है? <a href="<?php echo esc_url(eh_signup_page_url()); ?>">New User — Sign up करें</a></p>
  </div>
  <ul class="eh-auth2-perks"><li>✔ 100% Free — कोई plan नहीं</li><li>✔ हर टेस्ट का रिज़ल्ट और History</li><li>✔ Live Typing में अपनी रैंक</li></ul>
</main>
<?php get_footer();
