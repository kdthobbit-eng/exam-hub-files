<?php
/** @var array $eh_exam  @var array $eh_cfg */
$lang = $eh_exam['lang'] === 'hi' ? 'hi' : 'en';
$u = wp_get_current_user();
$eh_win = eh_window($eh_exam['slug']);
$cfg = array_merge([
    'exam' => $eh_exam,
    'passages' => eh_passages($lang),
    'rest' => esc_url_raw(rest_url('eh/v1/')),
    'nonce' => wp_create_nonce('wp_rest'),
    'loggedIn' => is_user_logged_in(),
    'userName' => is_user_logged_in() ? $u->display_name : '',
    'win' => $eh_win,
    'avatar' => is_user_logged_in() ? (string) get_user_meta($u->ID, 'eh_avatar', true) : '',
], $eh_cfg ?? []);
wp_enqueue_script('eh-hindi', EH_URI . '/assets/eh-hindi.js', [], EH_VER, true);
wp_enqueue_script('eh-typing', EH_URI . '/assets/eh-typing.js', ['eh-hindi'], EH_VER, true);
wp_add_inline_script('eh-hindi', 'window.EH_TEST=' . wp_json_encode($cfg, JSON_UNESCAPED_UNICODE) . ';', 'before');
?>
<div class="eh-testbox">
  <!-- Setup -->
  <div id="eh-setup" class="eh-panel eh-setup">
    <div class="eh-setup-l">
      <h2>Test शुरू करें</h2>
      <p class="muted">Language और Passage चुनें — टेस्ट असली एग्ज़ाम जैसी स्क्रीन पर खुलेगा।</p>
      <div class="eh-rules">
        <span>🎯 Target: <b><?php echo esc_html(eh_target_label($eh_exam)); ?></b></span>
        <span>⏱ Time: <b><?php echo (int) $eh_exam['minutes']; ?> min</b></span>
        <span>❗ Allowed error: <b><?php echo esc_html($eh_exam['allowed']); ?>%</b></span>
      </div>
    </div>
    <div class="eh-setup-r">
      <label>Select Language<select id="eh-opt-layout"></select></label>
      <label>Select Passage<select id="eh-opt-passage"></select></label>
      <label>Time (min)<input id="eh-opt-min" type="number" min="1" max="60"></label>
      <?php if (!is_user_logged_in()): ?><label>आपका नाम<input id="eh-guest-name" type="text" maxlength="40" placeholder="जैसे: Rahul"></label><?php endif; ?>
      <?php if ($lang === 'hi'): ?><p class="eh-tip">💡 Krutidev / Remington में कीबोर्ड <b>English</b> पर ही रखें। Mangal वाले Inscript चुनें।</p><?php endif; ?>
      <button id="eh-start" class="btn dark lg block">Start Test →</button>
      <?php if (!is_user_logged_in()): ?><p class="muted small center">रिज़ल्ट सेव रखने के लिए <a href="<?php echo esc_url(eh_login_page_url(home_url($_SERVER['REQUEST_URI'] ?? '/'))); ?>">Login</a> करें।</p><?php endif; ?>
    </div>
  </div>

  <!-- Exam window (full screen) -->
  <div id="eh-test" class="eh-exam <?php echo esc_attr(eh_window_classes($eh_win)); ?>" data-family="<?php echo esc_attr($eh_win['family']); ?>" style="--ph:<?php echo (int) $eh_win['ph']; ?>px;--th:<?php echo (int) $eh_win['th']; ?>px" hidden>
    <div class="ex-top" id="eh-ex-title">Typing Test</div>
    <div class="ex-strip"><?php echo esc_html($eh_exam['name']); ?></div>
    <div class="ex-head">
      <div class="ex-tool" role="toolbar" aria-label="Typing tools">
        <button type="button" data-act="mute" title="Sound on/off"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path class="snd-x" d="m23 9-6 6M17 9l6 6"/><path class="snd-on" d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg></button>
        <button type="button" data-act="full" title="Full screen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg></button>
        <button type="button" data-act="minus" title="Font size कम करें">A-</button>
        <button type="button" data-act="reset" title="Normal font size" class="a0">A</button>
        <button type="button" data-act="plus" title="Font size बढ़ाएँ">A+</button>
      </div>
      <div class="ex-time">Time left:- <b id="eh-timer">00:00</b></div>
      <div class="ex-photo"><span id="eh-cand-av"></span><b id="eh-cand-name">CANDIDATE</b></div>
    </div>
    <div class="ex-body">
      <div class="ex-main">
        <div class="ex-bar"><span>Keyboard Layout: <b id="eh-kb">QWERTY</b></span><span>Language: <b id="eh-layout-name">English</b></span></div>
        <div class="ex-box">
          <div id="eh-passage" class="eh-passage"></div>
          <textarea id="eh-input" class="eh-input" spellcheck="false" autocomplete="off" autocorrect="off" autocapitalize="off"></textarea>
        </div>
      </div>
      <aside class="ex-stats" id="eh-stats">
        <div class="ex-st-h">Typing Stats <label class="sw"><input type="checkbox" id="eh-st-on" checked><i></i></label></div>
        <div class="ex-st-body">
          <p>Keystrokes Count: <b id="st-kc">0</b></p>
          <p>Keystrokes Typed: <b id="st-kt">0</b></p>
          <p>Backspace Count: <b id="st-bk">0</b></p>
          <p>Keystrokes Error: <b id="st-ke">0</b></p>
          <p>Total Word Error: <b id="st-we">0</b></p>
          <p>Total Word Count: <b id="st-wc">0</b></p>
          <p>Typed Word Count: <b id="st-tw">0</b></p>
          <p>Pending Word Count: <b id="st-pw">0</b></p>
        </div>
      </aside>
    </div>
    <div class="ex-foot"><button type="button" id="eh-cancel" class="ex-btn red">Cancel</button><button type="button" id="eh-submit" class="ex-btn blue">Submit</button></div>
    <button type="button" id="eh-gear" class="ex-gear" title="Typing Settings">⚙</button>

    <div id="eh-drawer" class="ex-drawer" hidden>
      <div class="ex-dr-h"><b>Typing Settings</b><button type="button" data-close>✕</button></div>
      <details open><summary>▦ Appearance &amp; Layout</summary>
        <div class="ex-row"><label class="tg">TCS Layout <input type="checkbox" id="set-tcs" checked><i></i></label>
          <span class="ex-fs">Font Size (<b id="set-fs-v">16</b> pt) <button type="button" id="eh-fs-minus">A-</button><button type="button" id="eh-fs-plus">A+</button></span></div>
      </details>
      <details><summary>✎ Typing Behavior</summary>
        <div class="ex-grid">
          <label class="tg">BackSpace <input type="checkbox" id="set-bks" checked><i></i></label>
          <label class="tg">Limited Backspace <input type="checkbox" id="set-lbks"><i></i></label>
          <label class="tg">Word Highlight <input type="checkbox" id="set-hl"><i></i></label>
          <label class="tg">Auto Scroll <input type="checkbox" id="set-as"><i></i></label>
          <label class="tg">Extra Space <input type="checkbox" id="set-xs" checked><i></i></label>
        </div>
      </details>
      <details><summary>👁 Online Typing Passage</summary>
        <div class="ex-row"><label class="tg">Screen Typing (Passage दिखे) <input type="checkbox" id="set-scr" checked><i></i></label></div>
      </details>
      <details><summary>🖥 Sound &amp; View</summary>
        <div class="ex-row"><label class="tg">Typing Sound <input type="checkbox" id="set-snd"><i></i></label><button type="button" id="eh-full" class="ex-mini">⛶ Fullscreen</button></div>
      </details>
      <button type="button" class="ex-btn blue ex-dr-close" data-close>Close</button>
    </div>
  </div>

  <!-- Result -->
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
