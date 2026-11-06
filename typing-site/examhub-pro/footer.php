</main>
<section class="eh-cta-strip">
  <div class="wrap">
    <div><h3>Typing Updates सबसे पहले पाएं</h3><p>Telegram से जुड़िए — एग्ज़ाम नोटिस, नए पैसेज और टाइपिंग टिप्स वहीं मिलेंगे।</p></div>
    <?php echo eh_tg_buttons(); ?>
  </div>
</section>
<footer class="eh-footer">
  <div class="wrap eh-foot-grid">
    <div>
      <a class="eh-logo" href="<?php echo esc_url(home_url('/')); ?>"><span class="eh-logo-mark">EH</span><span class="eh-logo-txt"><?php echo esc_html(eh_opt('brand')); ?></span></a>
      <p class="muted"><?php echo esc_html(eh_opt('tagline')); ?></p>
    </div>
    <div><h4>Links</h4><ul>
      <li><a href="<?php echo esc_url(home_url('/exams/')); ?>">All Exams</a></li>
      <li><a href="<?php echo esc_url(home_url('/demo-typing/')); ?>">Demo Typing</a></li>
      <li><a href="<?php echo esc_url(home_url('/live-typing/')); ?>">Daily Live Typing</a></li>
      <li><a href="<?php echo esc_url(home_url('/results/')); ?>">Exam Based Result</a></li>
      <li><a href="<?php echo esc_url(home_url('/category/typing-tips/')); ?>">Typing Tips</a></li>
    </ul></div>
    <div><h4>Legal</h4><ul>
      <li><a href="<?php echo esc_url(home_url('/about/')); ?>">हमारे बारे में</a></li>
      <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">संपर्क करें</a></li>
      <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a></li>
      <li><a href="<?php echo esc_url(home_url('/terms/')); ?>">नियम और शर्तें</a></li>
      <li><a href="<?php echo esc_url(home_url('/disclaimer/')); ?>">अस्वीकरण</a></li>
    </ul></div>
    <div><h4>Social</h4><ul>
      <li><a href="<?php echo esc_url(eh_opt('tg_channel')); ?>" target="_blank" rel="noopener">Telegram Channel</a></li>
      <li><a href="<?php echo esc_url(eh_opt('tg_group')); ?>" target="_blank" rel="noopener">Telegram Group</a></li>
    </ul></div>
  </div>
  <div class="wrap eh-copy">© <?php echo wp_date('Y'); ?> <?php echo esc_html(eh_opt('brand')); ?> · erojgarresult.com · 100% Free</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
