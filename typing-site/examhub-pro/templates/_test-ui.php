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
  $eh_lvl = ['B' => 'Basic', 'E' => 'Easy', 'M' => 'Moderate', 'H' => 'Hard', 'S' => 'Super Hard'];
  $eh_row = function ($p, $checked = false) use ($eh_lvl) {
      return '<label class="eh-prow"><input type="radio" name="eh-psg" value="' . esc_attr($p['id']) . '"' . ($checked ? ' checked' : '') . '>'
          . '<span class="eh-lvl ' . esc_attr($p['level'] === 'B' ? 'easy' : str_replace(' ', '', strtolower($eh_lvl[$p['level']] ?? 'moderate'))) . '">' . esc_html($p['level'] === 'B' ? 'Easy' : ($eh_lvl[$p['level']] ?? '')) . '</span>'
          . '<span class="t">' . esc_html($p['title']) . '</span><span class="n">' . number_format_i18n($p['chars']) . ' keys · ' . (int) $p['words'] . ' words</span></label>';
  };
  ?>
  <div id="eh-setup" class="eh-setup2">
    <ol class="eh-steps-nav"><li class="on" data-go="2">1. Choose Passage</li><li data-go="1">2. Instructions</li></ol>

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
        <li>The test is <b><?php echo (int) $eh_exam['minutes']; ?> minutes</b> long. The timer starts as soon as the typing screen opens; time left is shown at the top right.</li>
        <li><?php echo $eh_win['layout'] === 'nopass' ? 'In this exam the passage is not shown on screen; you type from a printed copy. For practice the passage is shown on screen; press the "Passage" button at the top to hide or show it.' : 'The passage is shown in the top box. Type it in the box below.'; ?></li>
        <li>Copy, paste, right-click and drag are disabled. Only what you type is counted.</li>
        <?php if (($eh_exam['eval'] ?? '') === 'upsssc'): ?>
        <li>These rules are from the commission's typing test instructions (Advt. 12-Exam/2024 admit card).</li>
        <li>Backspace works only on the word you are typing and the <b>one word before it</b> (2 words in all). Older words are locked.</li>
        <li>Speed = (key depressions ÷ 5 − penalty) ÷ <?php echo (int) $eh_exam['minutes']; ?> minutes. The <b>first 5 mistakes are free</b>; every mistake after that cuts <b>6 words</b> (the wrong word + 5). You need <b><?php echo (int) ($eh_exam['wpm'] ?? 0); ?> WPM</b> to qualify.</li>
        <li>Two words typed in the wrong order count as a <b>half mistake</b>.</li>
        <li>Do not use the Ctrl key. Keep typing until the time is over; there is no break once the test starts.</li>
        <?php else: ?>
        <li>Backspace is on. You can turn it off or limit it in ⚙ Settings.</li>
        <?php endif; ?>
        <li>Use one space between words. Extra spaces, wrong capitals or missing punctuation count as a <b>half mistake</b>.</li>
        <li>A wrong word, a missed word or an added word counts as a <b>full mistake</b>.</li>
        <?php if (($eh_exam['eval'] ?? '') !== 'upsssc'): ?>
        <li>Error % = (Full mistake + Half mistake ÷ 2) ÷ words typed × 100</li>
        <li>To qualify you need <?php echo (($eh_exam['eval'] ?? '') === 'uppolice' && !empty($eh_exam['wpm'])) ? '<b>' . (int) $eh_exam['wpm'] . ' WPM</b> net speed (finishing the passage is not needed)' : ($eh_unit === 'keys' ? 'at least <b>' . number_format_i18n($eh_exam['target']) . ' key depressions</b>' : 'about <b>' . number_format_i18n($eh_exam['target']) . ' words</b>'); ?> with errors within <b><?php echo esc_html($eh_exam['allowed']); ?>%</b>.</li>
        <?php endif; ?>
        <li>The test submits on its own when time is up. To finish early, press <b>Submit</b> twice.</li>
        <li><b>Cancel</b> ends the test without saving a result.</li>
        <li>Font size, sound and full screen can be changed from the toolbar or ⚙ Settings.</li>
        <li>Once you reach the passage's word count, the space bar stops adding words. Use any time left to fix mistakes.</li>
        <li>When logged in, every test is saved in your <b>Typing History</b>, where you can retake it.</li>
        <?php if (!empty($eh_exam['retype'])): ?><li>This is a retyping exam: type the passage on screen exactly as given.</li><?php endif; ?>
      </ol>
      <div class="eh-ins-method">
        <?php if ($lang === 'hi'): ?>
        <div id="eh-im" class="eh-im" hidden>
          <p><b>Hindi - Mangal Inscript</b> - choose keyboard input method:</p>
          <label><input type="radio" name="eh-im" value="inscript" checked> <b>Built-in Keyboard Engine</b> — no software needed. Keep your keyboard in English and type Inscript.</label>
          <label><input type="radio" name="eh-im" value="external"> <b>External InScript Keyboard</b> — use the Hindi Inscript keyboard from your Windows language settings.</label>
          <label class="eh-im-chk" hidden><input type="checkbox" id="eh-im-ok"> I have turned on the Hindi - Mangal InScript keyboard on my system.</label>
        </div>
        <?php endif; ?>
        <label class="eh-agree"><input type="checkbox" id="eh-agree"> I have read all the instructions above</label>
        <button type="button" id="eh-start" class="btn dark lg" disabled>Start Typing →</button>
        <button type="button" class="btn ghost" data-go="2">← Change passage</button>
      </div>
    </div>

    <div id="eh-step2" class="eh-panel eh-stp">
      <div class="eh-psel">
        <div class="eh-psel-l">
          <?php if (!empty($cfg['fixedPassage'])):
              foreach ($cfg['passages'] as $p) if ($p['id'] === $cfg['fixedPassage']) { echo '<h3>Today’s passage</h3>' . $eh_row($p, true); } ?>
          <?php else: ?>
          <div class="eh-psel-top"><h3>Choose a passage</h3>
            <?php if ($eh_special): ?><button type="button" class="btn ghost sm" id="eh-sp-open">★ Special Passages</button><?php endif; ?></div>
          <p class="eh-lvl-legend"><span class="eh-lvl superhard">Super Hard</span> <span class="eh-lvl easy">Easy</span> <span class="eh-lvl moderate">Moderate</span> <span class="eh-lvl hard">Hard</span> · <span class="muted">20 new passages daily — pick a date</span></p>
          <?php if ($eh_days): $eh_lab = function ($i, $d) { return $i === 0 ? 'Today' : ($i === 1 ? 'Yesterday' : wp_date('l', strtotime($d . ' 12:00'))); }; ?>
          <div class="eh-dp eh-pday-dp" id="eh-pday-dp">
            <button type="button" class="eh-dp-nav off" data-dgo="1" aria-label="Previous day">‹</button>
            <button type="button" class="eh-dp-btn" id="eh-pday-btn"><span class="eh-dp-ic">📅</span><span class="eh-dp-t"><b><?php echo esc_html(wp_date('j M Y', strtotime($eh_days[0]['date'] . ' 12:00'))); ?></b><small>Today</small></span><span class="eh-dp-ar">▾</span></button>
            <button type="button" class="eh-dp-nav off" data-dgo="-1" aria-label="Next day">›</button>
            <div class="eh-dp-pop eh-pday-pop" id="eh-pday-pop" hidden>
              <?php foreach ($eh_days as $i => $day) echo '<button type="button" data-i="' . $i . '" class="' . ($i === 0 ? 'on' : '') . '"><b>' . esc_html(wp_date('j M Y', strtotime($day['date'] . ' 12:00'))) . '</b><small>' . esc_html($eh_lab($i, $day['date'])) . ' · ' . count($day['items']) . '</small></button>'; ?>
            </div>
          </div>
          <?php foreach ($eh_days as $i => $day): ?>
            <div class="eh-pday" data-i="<?php echo $i; ?>" data-d="<?php echo esc_attr(wp_date('j M Y', strtotime($day['date'] . ' 12:00'))); ?>" data-l="<?php echo esc_attr($eh_lab($i, $day['date'])); ?>"<?php echo $i ? ' hidden' : ''; ?>>
              <?php foreach (['S' => 'Super Hard', 'E' => 'Easy', 'M' => 'Moderate', 'H' => 'Hard'] as $lk => $ln):
                $its = array_values(array_filter($day['items'], function ($p) use ($lk) { $l = $p['level'] ?? 'M'; return ($l === 'B' ? 'E' : $l) === $lk; }));
                if (!$its) continue; ?>
                <p class="eh-pday-h"><span class="eh-lvl <?php echo str_replace(' ', '', strtolower($ln)); ?>"><?php echo $ln; ?></span> <span class="muted"><?php echo count($its); ?> passages</span></p>
                <?php foreach ($its as $p) echo $eh_row($p); ?>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
          <script>(function(){var box=document.getElementById('eh-pday-dp');if(!box)return;var days=[].slice.call(document.querySelectorAll('.eh-pday')),pop=document.getElementById('eh-pday-pop'),btn=document.getElementById('eh-pday-btn'),cur=0;
          function show(i){if(i<0||i>=days.length)return;cur=i;days.forEach(function(d,k){d.hidden=k!==i;});var d=days[i];btn.querySelector('b').textContent=d.getAttribute('data-d');btn.querySelector('small').textContent=d.getAttribute('data-l');
          box.querySelector('[data-dgo="1"]').classList.toggle('off',i>=days.length-1);box.querySelector('[data-dgo="-1"]').classList.toggle('off',i<=0);
          [].forEach.call(pop.querySelectorAll('button'),function(b){b.classList.toggle('on',+b.getAttribute('data-i')===i);});pop.hidden=true;
          var sel=document.querySelector('input[name=eh-psg]:checked');if(!sel||sel.closest('.eh-pday')!==d){var f=d.querySelector('input[name=eh-psg]');if(f){f.checked=true;f.dispatchEvent(new Event('change',{bubbles:true}));}}}
          box.addEventListener('click',function(e){var t=e.target.closest('button');if(!t)return;e.stopPropagation();if(t===btn){pop.hidden=!pop.hidden;return;}if(t.hasAttribute('data-dgo')){show(cur+(+t.getAttribute('data-dgo')));return;}if(t.hasAttribute('data-i'))show(+t.getAttribute('data-i'));});
          document.addEventListener('click',function(){pop.hidden=true;});show(0);
          window.addEventListener('load',function(){var c=document.querySelector('input[name=eh-psg]:checked'),d=c&&c.closest('.eh-pday');if(d&&d.hidden)show(+d.getAttribute('data-i'));});})();</script>
          <?php endif; ?>
          <?php endif; ?>
          <input type="hidden" id="eh-opt-passage" value="">
        </div>
        <div class="eh-psel-r">
          <label>Language / Keyboard<select id="eh-opt-layout"></select></label>
          <label>Time (minutes)<select id="eh-opt-min"><?php foreach (array_unique([5, 10, 15, 20, 25, 30, (int) $eh_exam['minutes']]) as $m) echo '<option value="' . $m . '">' . $m . ' min' . ($m === (int) $eh_exam['minutes'] ? ' (Exam)' : '') . '</option>'; ?></select></label>
          <label>Input limit<span class="eh-lim"><input id="eh-opt-limit" type="number" min="0" step="50" placeholder="Full"><select id="eh-opt-unit"><option value="keys">Characters</option><option value="words">Words</option></select></span><small class="muted">Set to the exam target. Leave empty for a full-length passage.</small></label>
          <?php if (!is_user_logged_in()): ?><label>Your name<input id="eh-guest-name" type="text" maxlength="40" placeholder="e.g. Rahul"></label><?php endif; ?>
          <button type="button" id="eh-next" class="btn dark lg block">Next →</button>
          <?php if (!is_user_logged_in()): ?><p class="muted small center"><a href="<?php echo esc_url(eh_login_page_url(home_url($_SERVER['REQUEST_URI'] ?? '/'))); ?>">Log in</a> to save your results.</p><?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($eh_special): ?>
    <div id="eh-sp-modal" class="eh-modal" hidden><div class="eh-modal-box">
      <div class="eh-modal-h"><b>★ Special Passages</b> <span class="muted small">Court orders, office orders and more</span><button type="button" data-x>✕</button></div>
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
        <button type="button" data-act="mute" title="Sound on/off"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path class="snd-x" d="m23 9-6 6M17 9l6 6"/><path class="snd-on" d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg><span class="eh-snd-lab">Sound Off</span></button>
        <button type="button" data-act="full" title="Full screen"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg></button>
        <button type="button" data-act="psg" class="eh-psg-btn" title="Show / hide the passage on screen">Passage</button>
        <button type="button" data-act="minus" title="Decrease font size">A-</button>
        <button type="button" data-act="reset" title="Normal font size" class="a0">A</button>
        <button type="button" data-act="plus" title="Increase font size">A+</button>
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
    <div class="eh-res-actions"><button id="eh-again" class="btn dark">Take test again</button><button id="eh-retype" class="btn ghost" type="button">Retype same passage</button><a id="eh-res-link" class="btn ghost" hidden href="#">🔗 Result link</a><a class="btn ghost" href="<?php echo esc_url(home_url('/exam-based-result/')); ?>">Leaderboard</a><?php echo eh_tg_buttons('inline'); ?></div>
    <?php if (eh_is_3284_exam($eh_exam ?? [])) echo eh_video_3284('in-dash'); ?>
  </div>
</div>
