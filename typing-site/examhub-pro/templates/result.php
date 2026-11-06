<?php
$parts = explode('-', (string) get_query_var('eh_slug'), 2);
$row = count($parts) === 2 ? eh_get_result((int) $parts[0], $parts[1]) : null;
if (!$row) { global $wp_query; $wp_query->set_404(); status_header(404); include get_404_template(); exit; }
$X = $row['exam'] === 'demo' ? ['slug' => 'demo', 'name' => 'Demo Typing Test', 'unit' => 'words', 'target' => 250, 'minutes' => 5, 'allowed' => (float) eh_opt('allowed_error'), 'lang' => 'en'] : (eh_exam($row['exam']) ?: ['slug' => $row['exam'], 'name' => $row['exam'], 'unit' => 'words', 'target' => 0, 'minutes' => 10, 'allowed' => 5, 'lang' => 'en']);
$R = [
    'net' => (int) $row['net'], 'gross' => (int) $row['gross'], 'acc' => (float) $row['acc'], 'err' => (float) $row['err'],
    'keys' => (int) $row['keys_count'], 'kerr' => (int) $row['kerr'], 'words' => (int) $row['words'], 'full' => (int) $row['full_m'],
    'half' => (int) $row['half_m'], 'backspaces' => (int) $row['backspaces'], 'minutes' => (float) $row['minutes'], 'pass' => (int) $row['pass'],
    'title' => $row['title'], 'date' => wp_date('d M Y, h:i A', strtotime($row['created'])), 'name' => eh_display_name($row),
];
wp_enqueue_script('eh-eval', EH_URI . '/assets/eh-eval.js', [], EH_VER, true);
wp_add_inline_script('eh-eval', 'window.EH_RES=' . wp_json_encode(['R' => $R, 'X' => $X, 'src' => (string) $row['src'], 'typed' => (string) $row['typed']], JSON_UNESCAPED_UNICODE) . ';', 'before');
wp_add_inline_script('eh-eval', <<<JS
(function(){var D=window.EH_RES,E=window.EHEval,el=document.getElementById('eh-res-dash');
var src=D.src?D.src.split(/\\s+/):[],typ=D.typed?D.typed.trim().split(/\\s+/):[];
var ops=typ.length?E.compare(src,typ):[];
D.R.pending=0;el.innerHTML=E.dashboard(D.R,D.X,ops);E.bindDash(el);})();
JS);
get_header(); ?>
<section class="wrap eh-section">
  <p class="muted small"><a href="<?php echo esc_url(home_url('/my-dashboard/')); ?>">← Typing History</a> · Candidate: <b><?php echo esc_html($R['name']); ?></b></p>
  <div class="eh-panel eh-dash"><div id="eh-res-dash"></div>
  <?php if (!$row['typed']): ?><p class="eh-tip">इस पुराने टेस्ट की टाइप की गई कॉपी सेव नहीं है, इसलिए शब्द-दर-शब्द तुलना नहीं दिख रही।</p><?php endif; ?>
  <div class="eh-res-actions">
    <a class="btn dark" href="<?php echo esc_url(add_query_arg(array_filter(['passage' => $row['passage']]), $X['slug'] === 'demo' ? home_url('/demo-typing/') : home_url('/typing/' . $X['slug'] . '/'))); ?>">यही पैसेज फिर से टाइप करें</a>
    <a class="btn ghost" href="<?php echo esc_url(home_url('/exam/' . $X['slug'] . '/')); ?>">Exam page</a>
    <?php echo eh_tg_buttons('inline'); ?>
  </div></div>
</section>
<?php get_footer();
