</main>
<section class="eh-cta-strip">
  <div class="wrap">
    <div><h3>Get exam updates first</h3><p>Join us on Telegram for exam notices, new batches, free PDFs and typing tips.</p></div>
    <?php echo eh_tg_buttons(); ?>
  </div>
</section>
<footer class="eh-footer">
  <div class="wrap eh-foot-grid">
    <div>
      <a class="eh-logo" href="<?php echo esc_url(home_url('/')); ?>"><?php echo eh_logo(); ?></a>
      <p class="muted">नौकरी लगने से पहले का घर</p>
      <img class="eh-qr" src="<?php echo esc_url(EH_URI . '/assets/qr-site.svg'); ?>" width="104" height="104" alt="QR code — <?php echo esc_attr(wp_parse_url(home_url(), PHP_URL_HOST)); ?>" loading="lazy">
    </div>
    <div><h4>Links</h4><ul>
      <li><a href="<?php echo esc_url(home_url('/exams/')); ?>">Exams</a></li>
      <li><a href="<?php echo esc_url(home_url('/exam-based-result/')); ?>">Exam Based Result</a></li>
      <li><a href="<?php echo esc_url(home_url('/faqs/')); ?>">FAQs</a></li>
      <li><a href="<?php echo esc_url(home_url('/blog/')); ?>">Typing Tips (Blog)</a></li>
      <li><a href="<?php echo esc_url(home_url('/demo-typing/')); ?>">Demo Typing</a></li>
    </ul></div>
    <div><h4>Legal</h4><ul>
      <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>">About us</a></li>
      <li><a href="<?php echo esc_url(home_url('/declaration/')); ?>">Declaration</a></li>
      <li><a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>">Terms of service</a></li>
      <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a></li>
      <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>">Contact us</a></li>
    </ul></div>
    <div><h4>Social</h4>
      <p class="muted small">Join for exam and typing updates only.</p>
      <div class="eh-social">
        <a href="<?php echo esc_url(eh_opt('tg_channel')); ?>" target="_blank" rel="noopener" title="Telegram Channel"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M9.8 15.3 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.8.4 1.3.2 1.5-.7l2.7-12.7c.3-1.1-.4-1.6-1.2-1.3L3.7 10.8c-1.1.4-1.1 1.1-.2 1.4l4.1 1.3 9.5-6c.4-.3.9-.1.6.2z"/></svg></a>
        <a href="<?php echo esc_url(eh_opt('tg_group')); ?>" target="_blank" rel="noopener" title="Telegram Group"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M9.8 15.3 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.8.4 1.3.2 1.5-.7l2.7-12.7c.3-1.1-.4-1.6-1.2-1.3L3.7 10.8c-1.1.4-1.1 1.1-.2 1.4l4.1 1.3 9.5-6c.4-.3.9-.1.6.2z"/></svg><i>G</i></a>
        <a href="<?php echo esc_url(eh_opt('youtube')); ?>" target="_blank" rel="noopener" title="YouTube"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8c1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3z"/></svg></a>
        <a href="<?php echo esc_url(eh_wa_url()); ?>" target="_blank" rel="noopener" title="WhatsApp"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.4-.7-2.9-1.2-4.7-4.1-4.9-4.3-.1-.2-1.2-1.6-1.2-3s.8-2.2 1-2.5c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.3 0 .5l-.3.5-.4.5c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.7-.1l2 .9c.3.2.5.2.5.4.1.1.1.7-.1 1.3z"/></svg></a>
      </div>
    </div>
  </div>
  <div class="wrap eh-copy">© <?php echo wp_date('Y'); ?> <?php echo esc_html(eh_opt('brand')); ?> · <?php echo esc_html(wp_parse_url(home_url(), PHP_URL_HOST)); ?> · 100% Free</div>
  <button type="button" class="eh-totop" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top">↑</button>
</footer>

<?php wp_footer(); ?>
</body>
</html>
