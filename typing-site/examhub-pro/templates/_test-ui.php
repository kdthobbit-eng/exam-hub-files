<?php
/** @var array $eh_exam  @var array $eh_cfg */
$lang = $eh_exam['lang'] === 'hi' ? 'hi' : 'en';
$u = wp_get_current_user();
$eh_win = eh_window($eh_exam['slug'], $eh_exam['lang'] === 'en' ? 'en' : 'hi');
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
wp_enqueue_script('eh-eval', EH_URI . '/assets/eh-eval.js', [], EH_VER, true);
wp_enqueue_script('eh-typing', EH_URI . '/assets/eh-typing.js', ['eh-hindi', 'eh-eval'], EH_VER, true);
wp_add_inline_script('eh-hindi', 'window.EH_TEST=' . wp_json_encode($cfg, JSON_UNESCAPED_UNICODE) . ';', 'before');
?>
<div class="eh-testbox">
  <!-- Setup: step 1 instructions, step 2 passage -->
  <?php
  $eh_days = empty($cfg['fixedPassage']) ? eh_passage_days($lang, 7) : [];
  $eh_special = array_values(array_filter($cfg['passages'], function ($p) { return !empty($p['special']); }));
  $eh_unit = $eh_exam['unit'] === 'keys' ? 'keys' : 'words';
  $eh_lvl = ['B' => 'Basic', 'E' => 'Easy', 'M' => 'Moderate', 'H' => 'Hard'];
  $eh_row = function ($p, $checked = false) use ($eh_lvl) {
      return '<label class="eh-prow"><input type="radio" name="eh-psg" value="' . esc_attr($p['id']) . '"' . ($checked ? ' checked' : '') . '>'
          . '<span class="lv lv-' . esc_attr($p['level']) . '" title="' . esc_attr($eh_lvl[$p['level']] ?? '') . '">' . esc_html($p['level']) . '</span>'
          . '<span class="t">' . esc_html($p['title']) . '</span><span class="n">' . number_format_i18n($p['chars']) . ' keys · ' . (int) $p['words'] . ' words</span></label>';
  };
  ?>
  <div id="eh-setup" class="eh-setup2">
    <ol class="eh-steps-nav"><li class="on" data-go="2">1. पैसेज चुनें</li><li data-go="1">2. निर्देश (Instructions)</li></ol>

    <div id="eh-step1" class="eh-panel eh-stp" hidden>
      <div class="eh-ins-cards">
        <div><small>Personal Information</small><b id="eh-ic-name"><?php echo esc_html(is_user_logged_in() ? wp_get_current_user()->display_name : 'Guest'); ?></b></div>
        <div><small>Exam Description</small><b><?php echo esc_html($eh_exam['name']); ?></b></div>
        <div><small>Passage Detail</small><b id="eh-ic-psg">—</b></div>
      </div>
      <div class="eh-ins-head">
        <div><h2><?php echo esc_html($eh_exam['name']); ?></h2>
        <p class="eh-rules">
          <span>🎯 Target: <b><?php echo esc_html(eh_target_label($eh_exam)); ?></b></span>
          <span>⏱ Time: <b><?php echo (int) $eh_exam['minutes']; ?> min</b></span>
          <span>❗ Allowed error: <b><?php echo esc_html($eh_exam['allowed']); ?>%</b></span>
          <span>🖥 Window: <b><?php echo esc_html(strtoupper($eh_win['family'])); ?></b></span>
        </p></div>
      </div>
      <ol class="eh-ins">
        <li>टेस्ट <b><?php echo (int) $eh_exam['minutes']; ?> मिनट</b> का है। असली एग्ज़ाम की तरह टाइमर स्क्रीन खुलते ही चालू हो जाएगा — ऊपर दाईं ओर बचा समय दिखेगा।</li>
        <li><?php echo $eh_win['layout'] === 'nopass' ? 'इस एग्ज़ाम में स्क्रीन पर पैसेज नहीं दिखता, असली एग्ज़ाम की तरह छपे पैसेज से देखकर टाइप करना होता है। प्रैक्टिस के लिए ⚙ Settings में "Screen Typing" चालू करके पैसेज देख सकते हैं।' : 'ऊपर वाले बॉक्स में पैसेज दिखेगा, उसे देखकर नीचे वाले बॉक्स में टाइप करें।'; ?></li>
        <li>Copy-paste, right click और drag बंद हैं — जो टाइप करेंगे वही गिना जाएगा।</li>
        <li>Backspace चालू है। असली एग्ज़ाम की तरह बंद या सीमित करना हो तो ⚙ Settings से बदल लें।</li>
        <li>हर शब्द के बीच एक ही स्पेस दें। दो-दो स्पेस, गलत कैपिटल या छूटा कॉमा-फुलस्टॉप <b>Half mistake</b> गिना जाता है।</li>
        <li>गलत शब्द, छूटा हुआ शब्द और अपनी तरफ़ से जोड़ा गया फालतू शब्द <b>Full mistake</b> गिना जाता है।</li>
        <li>Error % = (Full mistake + Half mistake ÷ 2) ÷ कुल टाइप किए शब्द × 100</li>
        <li>Qualify होने के लिए <?php echo $eh_unit === 'keys' ? 'कम से कम <b>' . number_format_i18n($eh_exam['target']) . ' key depressions</b>' : 'करीब <b>' . number_format_i18n($eh_exam['target']) . ' शब्द</b>'; ?> और error <b><?php echo esc_html($eh_exam['allowed']); ?>%</b> के अंदर होना चाहिए।</li>
        <li>समय पूरा होते ही टेस्ट अपने आप सबमिट हो जाएगा। पहले खत्म करना हो तो <b>Submit</b> दो बार दबाएं।</li>
        <li><b>Cancel</b> दबाने पर टेस्ट रद्द हो जाएगा और रिज़ल्ट सेव नहीं होगा।</li>
        <li>Font छोटा-बड़ा, आवाज़ और Full screen ऊपर के टूलबार या ⚙ Settings से कर सकते हैं।</li>
        <li>पैसेज जितने शब्दों का है, उतने शब्द पूरे होने के बाद स्पेस बार आगे शब्द नहीं जोड़ेगा। पैसेज पूरा हो जाए तो दोबारा टाइप करने की ज़रूरत नहीं — बचे समय में गलतियां सुधार सकते हैं।</li>
        <li>Login करके दिया गया हर टेस्ट आपकी <b>Typing History</b> में सेव रहता है, वहीं से दोबारा भी दे सकते हैं।</li>
        <?php if (!empty($eh_exam['retype'])): ?><li>ये Retyping वाला एग्ज़ाम है — स्क्रीन पर दिया पैसेज ही हूबहू टाइप करना होता है।</li><?php endif; ?>
      </ol>
      <div class="eh-ins-method">
        <label>Keyboard / Language<select id="eh-opt-layout"></select></label>
        <?php if ($lang === 'hi'): ?><p class="eh-tip">💡 <b>Krutidev / Remington</b>: कंप्यूटर का कीबोर्ड English पर ही रखें, हिंदी हम खुद बना देंगे। <b>Inscript (अपना कीबोर्ड)</b>: अगर आपके कंप्यूटर में Windows का Hindi Inscript कीबोर्ड चालू है तो ये चुनें।</p><?php endif; ?>
        <label class="eh-agree"><input type="checkbox" id="eh-agree"> मैंने ऊपर दिए सभी निर्देश पढ़ लिए हैं</label>
        <button type="button" id="eh-start" class="btn dark lg" disabled>Start Typing →</button>
        <button type="button" class="btn ghost" data-go="2">← पैसेज बदलें</button>
      </div>
    </div>

    <div id="eh-step2" class="eh-panel eh-stp">
      <div class="eh-psel">
        <div class="eh-psel-l">
          <?php if (!empty($cfg['fixedPassage'])):
              foreach ($cfg['passages'] as $p) if ($p['id'] === $cfg['fixedPassage']) { echo '<h3>आज का पैसेज</h3>' . $eh_row($p, true); } ?>
          <?php else: ?>
          <div class="eh-psel-top"><h3>पैसेज चुनें</h3>
            <?php if ($eh_special): ?><button type="button" class="btn ghost sm" id="eh-sp-open">★ Special Passages</button><?php endif; ?></div>
          <p class="eh-lvl-legend">B - Basic · E - Easy · M - Moderate · H - Hard · <span class="muted">रोज़ 6 नए पैसेज</span></p>
          <label class="eh-prow rnd"><input type="radio" name="eh-psg" value="" checked><span class="lv">?</span><span class="t">कोई भी (Random Passage)</span><span class="n"></span></label>
          <?php foreach ($eh_days as $i => $day): ?>
            <details class="eh-day" <?php echo $i < 2 ? 'open' : ''; ?>><summary><?php echo esc_html($i === 0 ? 'आज — ' : ($i === 1 ? 'कल — ' : '')); echo esc_html(wp_date('d M Y, l', strtotime($day['date'] . ' 12:00'))); ?> <span class="muted">(<?php echo count($day['items']); ?>)</span></summary>
              <?php foreach ($day['items'] as $p) echo $eh_row($p); ?>
            </details>
          <?php endforeach; ?>
          <?php endif; ?>
          <input type="hidden" id="eh-opt-passage" value="">
        </div>
        <div class="eh-psel-r">
          <label>Time (minutes)<select id="eh-opt-min"><?php foreach (array_unique([5, 10, 15, 20, 25, 30, (int) $eh_exam['minutes']]) as $m) echo '<option value="' . $m . '">' . $m . ' min' . ($m === (int) $eh_exam['minutes'] ? ' (Exam)' : '') . '</option>'; ?></select></label>
          <label>Input limit<span class="eh-lim"><input id="eh-opt-limit" type="number" min="0" step="50" placeholder="पूरा"><select id="eh-opt-unit"><option value="keys">Characters</option><option value="words">Words</option></select></span><small class="muted">पहले से एग्ज़ाम का target भरा है। खाली छोड़ें तो समय के हिसाब से लंबा पैसेज मिलेगा।</small></label>
          <?php if (!is_user_logged_in()): ?><label>आपका नाम<input id="eh-guest-name" type="text" maxlength="40" placeholder="जैसे: Rahul"></label><?php endif; ?>
          <button type="button" id="eh-next" class="btn dark lg block">आगे बढ़ें (Next) →</button>
          <?php if (!is_user_logged_in()): ?><p class="muted small center">रिज़ल्ट सेव रखने के लिए <a href="<?php echo esc_url(eh_login_page_url(home_url($_SERVER['REQUEST_URI'] ?? '/'))); ?>">Login</a> करें।</p><?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($eh_special): ?>
    <div id="eh-sp-modal" class="eh-modal" hidden><div class="eh-modal-box">
      <div class="eh-modal-h"><b>★ Special Passages</b> <span class="muted small">कोर्ट आदेश, ऑफ़िस ऑर्डर जैसे पैसेज</span><button type="button" data-x>✕</button></div>
      <?php foreach ($eh_special as $p) echo $eh_row($p); ?>
    </div></div>
    <?php endif; ?>
  </div>

  <!-- Exam window (full screen) -->
  <div id="eh-test" class="eh-exam <?php echo esc_attr(eh_window_classes($eh_win)); ?>" data-family="<?php echo esc_attr($eh_win['family']); ?>" style="<?php echo esc_attr(eh_window_style($eh_win)); ?>" hidden>
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
        <div class="ex-row"><span class="ex-fs">Font Size (<b id="set-fs-v">16</b> pt) <button type="button" id="eh-fs-minus">A-</button><button type="button" id="eh-fs-plus">A+</button></span>
          <input type="checkbox" id="set-tcs" checked hidden></div>
      </details>
      <details><summary>✎ Typing Behavior</summary>
        <div class="ex-grid">
          <label class="tg">BackSpace <input type="checkbox" id="set-bks" checked><i></i></label>
          <label class="tg">Limited Backspace <input type="checkbox" id="set-lbks"><i></i></label>
          <label class="tg">Word Highlight <input type="checkbox" id="set-whl" checked><i></i></label>
          <label class="tg">Current Word Highlight <input type="checkbox" id="set-hl" checked><i></i></label>
          <label class="tg">Limit Cancel <input type="checkbox" id="set-lc" checked><i></i></label>
          <label class="tg">Passage on Screen <input type="checkbox" id="set-scr" checked><i></i></label>
          <input type="checkbox" id="set-as" checked hidden><input type="checkbox" id="set-xs" hidden>
        </div>
      </details>
      <details><summary>🖥 Sound &amp; View</summary>
        <div class="ex-row ex-col"><input type="checkbox" id="set-snd" hidden>
          <button type="button" id="eh-snd-btn" class="ex-mini ex-teal">▷ Play Typing Sound</button>
          <button type="button" id="eh-full" class="ex-mini">⛶ Fullscreen</button></div>
      </details>
      <button type="button" class="ex-btn blue ex-dr-close" data-close>Close</button>
    </div>
  </div>

  <!-- Result -->
  <div id="eh-result" class="eh-panel eh-dash" hidden>
    <div id="eh-res-dash"></div>
    <p id="eh-res-saved" class="muted small"></p>
    <div class="eh-res-actions"><button id="eh-again" class="btn dark">दोबारा टेस्ट दें</button><button id="eh-retype" class="btn ghost" type="button">यही पैसेज फिर से</button><a id="eh-res-link" class="btn ghost" hidden href="#">🔗 रिज़ल्ट का लिंक</a><a class="btn ghost" href="<?php echo esc_url(home_url('/exam-based-result/')); ?>">Leaderboard</a><?php echo eh_tg_buttons('inline'); ?></div>
  </div>
</div>
