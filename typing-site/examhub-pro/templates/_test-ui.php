<?php
/** @var array $eh_exam  @var array $eh_cfg */
$lang = $eh_exam['lang'] === 'hi' ? 'hi' : 'en';
$cfg = array_merge([
    'exam' => $eh_exam,
    'passages' => eh_passages($lang),
    'rest' => esc_url_raw(rest_url('eh/v1/')),
    'nonce' => wp_create_nonce('wp_rest'),
    'loggedIn' => is_user_logged_in(),
], $eh_cfg ?? []);
wp_enqueue_script('eh-hindi', EH_URI . '/assets/eh-hindi.js', [], EH_VER, true);
wp_enqueue_script('eh-typing', EH_URI . '/assets/eh-typing.js', ['eh-hindi'], EH_VER, true);
wp_add_inline_script('eh-hindi', 'window.EH_TEST=' . wp_json_encode($cfg, JSON_UNESCAPED_UNICODE) . ';', 'before');
?>
<div class="eh-testbox">
  <div id="eh-setup" class="eh-panel">
    <h2>Test Setting</h2>
    <div class="eh-form">
      <label>Layout<select id="eh-opt-layout"></select></label>
      <label>Passage<select id="eh-opt-passage"></select></label>
      <label>Time (min)<input id="eh-opt-min" type="number" min="1" max="60"></label>
      <?php if (!is_user_logged_in()): ?><label>आपका नाम (रिज़ल्ट में दिखेगा)<input id="eh-guest-name" type="text" maxlength="40" placeholder="जैसे: Rahul"></label><?php endif; ?>
      <label class="chk"><input id="eh-opt-bks" type="checkbox" checked> Backspace allowed</label>
      <label class="chk"><input id="eh-opt-hl" type="checkbox" checked> Word highlight</label>
    </div>
    <?php if ($lang === 'hi'): ?>
    <p class="eh-tip">💡 Krutidev चुना है तो कीबोर्ड <b>English</b> पर ही रहने दें — टाइप करते ही हिंदी में दिखेगा। Mangal वाले Inscript चुनें।</p>
    <?php endif; ?>
    <div class="eh-rules">
      <span>🎯 Target: <b><?php echo esc_html(eh_target_label($eh_exam)); ?></b></span>
      <span>⏱ Time: <b><?php echo (int) $eh_exam['minutes']; ?> min</b></span>
      <span>❗ Allowed error: <b><?php echo esc_html($eh_exam['allowed']); ?>%</b></span>
    </div>
    <button id="eh-start" class="btn dark lg">Start Test →</button>
    <?php if (!is_user_logged_in()): ?><p class="muted small">अपने सारे रिज़ल्ट सेव रखने के लिए <a href="<?php echo esc_url(wp_login_url(home_url($_SERVER['REQUEST_URI'] ?? '/'))); ?>">Login</a> करें।</p><?php endif; ?>
  </div>

  <div id="eh-test" class="eh-panel" hidden>
    <div class="eh-testbar">
      <div><b><?php echo esc_html($eh_exam['name']); ?></b> <span class="muted">· <span id="eh-layout-name"></span></span></div>
      <div id="eh-live" class="muted"></div>
      <div id="eh-timer" class="eh-timer">00:00</div>
    </div>
    <div id="eh-passage" class="eh-passage"></div>
    <textarea id="eh-input" class="eh-input" spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off" placeholder="यहाँ टाइप करना शुरू करें — पहली key दबाते ही टाइमर चालू हो जाएगा"></textarea>
    <div class="eh-testfoot"><span class="muted small">Copy-paste और Arrow keys काम नहीं करेंगी</span><button id="eh-submit" class="btn dark">Submit</button></div>
  </div>

  <div id="eh-result" class="eh-panel" hidden>
    <div id="eh-res-head"></div>
    <div id="eh-res-grid" class="eh-res-grid"></div>
    <p id="eh-res-saved" class="muted small"></p>
    <h3>आपकी गलतियाँ</h3>
    <p class="eh-legend"><span class="d-ok">सही</span> <span class="d-sub">Full mistake <s>मूल</s></span> <span class="d-half">Half mistake</span> <span class="d-om">छूटा शब्द</span> <span class="d-add">फालतू शब्द</span></p>
    <div id="eh-res-diff" class="eh-diff <?php echo $lang === 'hi' ? 'hi' : ''; ?>"></div>
    <div class="eh-res-actions"><button id="eh-again" class="btn dark">दोबारा टेस्ट दें</button><a class="btn ghost" href="<?php echo esc_url(home_url('/results/')); ?>">Leaderboard</a><?php echo eh_tg_buttons('inline'); ?></div>
  </div>
</div>
