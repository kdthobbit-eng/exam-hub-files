<?php
/* Builds preview/index.html from the real theme templates with small stand-ins for WordPress.
   Run: php preview/build.php   (from typing-site/) */
$T = __DIR__ . '/../examhub-pro';
define('EH_URI', '../examhub-pro');
define('EH_DIR', __DIR__ . '/../examhub-pro');
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function home_url($p = '') { return 'https://xlstudy.in' . $p; }
function get_option($k, $d = null) { return $d; }
function is_user_logged_in() { return false; }
function is_front_page() { return true; }
function has_nav_menu($x) { return false; }
function number_format_i18n($n) { return number_format((float) $n); }
function wp_date($f, $t = null) { return date($f, $t ?? time()); }
function wp_parse_url($u, $c = -1) { return parse_url($u, $c); }
function eh_login_page_url() { return home_url('/login/'); }
function eh_signup_page_url() { return home_url('/signup/'); }
function language_attributes() { echo 'lang="en"'; }
function bloginfo($x) { echo 'UTF-8'; }
function body_class() { echo 'class="home"'; }
function wp_body_open() {}
function wp_head() {
    echo '<title>UPSSSC Alert home preview</title>' . "\n";
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap">' . "\n";
    echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Instrument+Serif:ital@0;1&family=Mukta:wght@400;500;600;700;800&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap">' . "\n";
    $v = time();
    echo '<link rel="stylesheet" href="../examhub-pro/style.css?v=' . $v . '"><link rel="stylesheet" href="../examhub-pro/assets/eh-home.css?v=' . $v . '">' . "\n";
}
function wp_footer() { $v = time(); echo '<script src="../examhub-pro/assets/eh-site.js?v=' . $v . '"></script><script src="../examhub-pro/assets/eh-home.js?v=' . $v . '"></script>'; }
function get_header() { global $T; include $T . '/header.php'; }
function get_footer() { global $T; include $T . '/footer.php'; }

/* real theme helpers, lifted from functions.php so the preview matches the site */
$src = file_get_contents($T . '/functions.php') . file_get_contents($T . '/inc/data.php');
foreach (['eh_opt', 'eh_exam_card', 'eh_tg_buttons', 'eh_member_links', 'eh_default_menu', 'eh_upcoming', 'eh_t', 'eh_wa_url', 'eh_last_em', 'eh_logo', 'eh_ja_first', 'eh_xl', 'eh_lang_label'] as $fn) {
    if (!preg_match('/^function ' . $fn . '\([^\n]*\}$/m', $src, $m) && !preg_match('/^function ' . $fn . '\(.*?\n\}/ms', $src, $m)) { fwrite(STDERR, "missing $fn\n"); exit(1); }
    eval($m[0]);
}
function eh_exams() { global $T; return json_decode(file_get_contents($T . '/data/exams.json'), true); }
function eh_exam($slug) { foreach (eh_exams() as $e) if ($e['slug'] === $slug) return $e; return null; }
function eh_ticker_items() { return [['Daily Live Typing is on: a new passage every day, check your rank', home_url('/live-typing/'), 'Live Typing →']]; }
function eh_attempt_counts() { $c = []; foreach (eh_exams() as $e) if (stripos($e['name'], 'UPSSSC') !== false || stripos($e['name'], 'SSC CHSL') !== false) $c[$e['slug']] = 40 + strlen($e['slug']); return $c; }
function eh_recent($n) { $x = eh_exams(); $r = []; for ($i = 0; $i < 8; $i++) $r[] = ['name' => 'Sample student ' . ($i + 1), 'user_id' => 0, 'exam' => $x[$i * 7]['slug'], 'gross' => 38 - $i, 'net' => 34 - $i]; return $r; }
function eh_display_name($r) { return $r['name']; }

ob_start();
include $T . '/front-page.php';
file_put_contents(__DIR__ . '/index.html', ob_get_clean());
echo "built preview/index.html\n";
