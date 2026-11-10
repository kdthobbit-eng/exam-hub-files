<?php
/**
 * Exam Hub Pro — typing test theme for xlstudy.in
 */
if (!defined('ABSPATH')) exit;

define('EH_VER', '1.0.' . (int) @filemtime(__DIR__ . '/style.css'));
define('EH_DIR', get_template_directory());
define('EH_URI', get_template_directory_uri());

require EH_DIR . '/inc/data.php';
require EH_DIR . '/inc/windows.php';
require EH_DIR . '/inc/info.php';
require EH_DIR . '/inc/blog.php';
require EH_DIR . '/inc/db.php';
require EH_DIR . '/inc/rest.php';
require EH_DIR . '/inc/admin.php';
require EH_DIR . '/inc/auth.php';

/* ---------- setup ---------- */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');
    register_nav_menus(['primary' => 'Header Menu', 'footer' => 'Footer Menu']);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('eh-fonts', 'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('eh-style', get_stylesheet_uri(), [], EH_VER);
    wp_enqueue_script('eh-site', EH_URI . '/assets/eh-site.js', [], EH_VER, true);
});

/* hide admin bar for normal users */
add_filter('show_admin_bar', function ($show) { return current_user_can('edit_posts') ? $show : false; });

/* ---------- options (chat/wp-cli se badal sakte hain) ---------- */
function eh_opt($key) {
    $defaults = [
        'tg_group'   => 'https://t.me/examhub2026',
        'tg_channel' => 'https://t.me/examhub26',
        'brand'      => 'Alert Typing',
        'tagline'    => 'Typing practice for government exams — real exam format, fully free',
        'notice'     => 'Daily Live Typing is on: a new passage every day, check your rank',
        'allowed_error' => 5,
    ];
    $v = get_option('eh_' . $key, null);
    return ($v === null || $v === '' || $v === false) ? ($defaults[$key] ?? '') : $v;
}

/* ---------- routes ---------- */
add_action('init', function () {
    add_rewrite_rule('^exams/?$', 'index.php?eh_view=exams', 'top');
    add_rewrite_rule('^exam/([^/]+)/?$', 'index.php?eh_view=exam&eh_slug=$matches[1]', 'top');
    add_rewrite_rule('^typing/([^/]+)/?$', 'index.php?eh_view=typing&eh_slug=$matches[1]', 'top');
    add_rewrite_rule('^demo-typing/?$', 'index.php?eh_view=typing&eh_slug=demo', 'top');
    add_rewrite_rule('^live-typing/?$', 'index.php?eh_view=live', 'top');
    add_rewrite_rule('^results/?$', 'index.php?eh_view=results', 'top');
    add_rewrite_rule('^my-dashboard/?$', 'index.php?eh_view=dashboard', 'top');
    add_rewrite_rule('^typing-history/?$', 'index.php?eh_view=dashboard', 'top');
    add_rewrite_rule('^result/([0-9]+-[a-z0-9]+)/?$', 'index.php?eh_view=result&eh_slug=$matches[1]', 'top');
    add_rewrite_rule('^exam-hall-typing/?$', 'index.php?eh_view=examhall', 'top');
    add_rewrite_rule('^download-passages/?$', 'index.php?eh_view=download', 'top');
    add_rewrite_rule('^typing-insight/?$', 'index.php?eh_view=insight', 'top');
    add_rewrite_rule('^blog/?$', 'index.php?eh_view=blog', 'top');
    add_rewrite_rule('^exam-based-result/?$', 'index.php?eh_view=results', 'top');
    add_rewrite_rule('^(faqs|about-us|declaration|terms-of-service|privacy-policy|contact-us)/?$', 'index.php?eh_view=info&eh_slug=$matches[1]', 'top');
    if (get_option('eh_rewrite_ver') !== '3') { flush_rewrite_rules(false); update_option('eh_rewrite_ver', '3'); }
});
add_filter('query_vars', function ($v) { $v[] = 'eh_view'; $v[] = 'eh_slug'; return $v; });

add_filter('pre_handle_404', function ($pre) { return get_query_var('eh_view') ? true : $pre; });

add_action('template_redirect', function () {
    $view = get_query_var('eh_view');
    if (!$view) return;
    global $wp_query;
    $slug = get_query_var('eh_slug');
    $file = EH_DIR . '/templates/' . sanitize_file_name($view) . '.php';
    $bad = !file_exists($file) || (in_array($view, ['exam', 'typing'], true) && $slug !== 'demo' && !eh_exam($slug));
    if ($bad) { $wp_query->set_404(); status_header(404); nocache_headers(); include get_404_template(); exit; }
    $wp_query->is_404 = false; $wp_query->is_home = false; $wp_query->is_page = false; $wp_query->is_singular = false;
    $wp_query->queried_object = null; $wp_query->queried_object_id = 0;
    status_header(200);
    include $file;
    exit;
}, 1);

add_filter('pre_get_document_title', 'eh_doc_title', 9999);
add_filter('rank_math/frontend/title', 'eh_doc_title', 9999);
function eh_doc_title($t) {
    $view = get_query_var('eh_view');
    if (!$view) return $t;
    $b = eh_opt('brand');
    $slug = get_query_var('eh_slug');
    switch ($view) {
        case 'exams': return 'All Typing Exams — ' . $b;
        case 'exam': $e = eh_exam($slug); return ($e ? $e['name'] : 'Exam') . ' — ' . $b;
        case 'typing': $e = $slug === 'demo' ? null : eh_exam($slug); return ($e ? $e['name'] . (stripos($e['name'], 'test') === false ? ' Test' : '') : 'Demo Typing Test') . ' — ' . $b;
        case 'live': return 'Daily Live Typing — ' . $b;
        case 'results': return 'Exam Based Result & Leaderboard — ' . $b;
        case 'dashboard': return 'Typing History — ' . $b;
        case 'result': return 'Performance Dashboard — ' . $b;
        case 'examhall': return 'Exam Hall Typing — ' . $b;
        case 'download': return 'Free Typing Passage PDF Download — ' . $b;
        case 'insight': return 'Typing Insight — ' . $b;
        case 'blog': return 'Exam Typing Tips and Rules — ' . $b;
        case 'info': $I = eh_info_pages(); return ($I[$slug]['title'] ?? 'Info') . ' — ' . $b;
    }
    return $t;
}

function eh_view_desc() {
    $view = get_query_var('eh_view');
    $desc = eh_opt('tagline');
    if ($view === 'exam' || $view === 'typing') {
        $e = eh_exam(get_query_var('eh_slug'));
        if ($e) $desc = $e['name'] . ' — ' . eh_target_label($e) . ' in ' . $e['minutes'] . ' minutes. Free online typing test in real exam format, with an instant result and full error details.';
    } elseif ($view === 'exams') $desc = 'SSC, High Court, UPSSSC, DSSSB, KVS, RRB and ' . count(eh_exams()) . '+ government exams. Free typing practice in English, Krutidev and Mangal.';
    return $desc;
}
function eh_view_url() { return home_url(strtok($_SERVER['REQUEST_URI'] ?? '/', '?')); }
add_filter('rank_math/frontend/description', function ($d) { return get_query_var('eh_view') ? eh_view_desc() : $d; }, 9999);
add_filter('rank_math/frontend/canonical', function ($c) { return get_query_var('eh_view') ? eh_view_url() : $c; }, 9999);
add_filter('rank_math/opengraph/url', function ($c) { return get_query_var('eh_view') ? eh_view_url() : $c; }, 9999);
add_filter('rank_math/frontend/robots', function ($r) { if (in_array(get_query_var('eh_view'), ['dashboard', 'result', 'insight'], true)) { $r['index'] = 'noindex'; } return $r; }, 9999);
add_action('wp_head', function () {
    if (defined('RANK_MATH_VERSION')) return;
    if (get_query_var('eh_view') || is_front_page()) echo '<meta name="description" content="' . esc_attr(eh_view_desc()) . '">' . "\n";
}, 1);

/* ---------- helpers for templates ---------- */
function eh_cat_label($c) {
    return ['court' => 'High Court / Court', 'central' => 'SSC / Railway / Forces', 'central-org' => 'Central Govt. Org', 'state' => 'State Exams', 'other' => 'Other'][$c] ?? $c;
}
function eh_lang_label($l) { return ['en' => 'English', 'hi' => 'Hindi', 'mr' => 'Marathi', 'or' => 'Odia'][$l] ?? $l; }
/* target speed in WPM as shown on exam sites (key based: keys ÷ minutes ÷ 10) */
function eh_target_wpm($e) {
    if (!$e) return 0;
    $m = max(1, (int) $e['minutes']);
    return $e['unit'] === 'keys' ? (int) round($e['target'] / $m / 10) : (int) round($e['target'] / $m);
}
function eh_row_speeds($r) {
    $min = max(1 / 60, (float) $r['minutes']);
    $g = (int) $r['words'] / $min; $n = max(0, (int) $r['words'] - ((int) $r['full_m'] + (int) $r['half_m'] / 2)) / $min;
    return [number_format($g, 2, '.', ''), number_format($n, 2, '.', '')];
}
function eh_target_label($e) { return number_format_i18n($e['target']) . ($e['unit'] === 'keys' ? ' keys' : ' words'); }

function eh_exam_card($e, $attempts = 0) {
    $url = home_url('/exam/' . $e['slug'] . '/');
    ob_start(); ?>
    <a class="eh-card exam" href="<?php echo esc_url($url); ?>" data-name="<?php echo esc_attr(strtolower($e['name'])); ?>" data-cat="<?php echo esc_attr($e['cat']); ?>" data-lang="<?php echo esc_attr($e['lang']); ?>">
      <div class="eh-card-top">
        <span class="badge lang-<?php echo esc_attr($e['lang']); ?>"><?php echo esc_html(eh_lang_label($e['lang'])); ?></span>
        <?php if (!empty($e['retype'])): ?><span class="badge retype">Retyping</span><?php endif; ?>
      </div>
      <h3 title="<?php echo esc_attr($e['name']); ?>"><?php echo esc_html($e['name']); ?></h3>
      <div class="eh-card-meta">
        <span><b><?php echo esc_html(number_format_i18n($e['target'])); ?></b> <?php echo $e['unit'] === 'keys' ? 'keys' : 'words'; ?></span>
        <span><b><?php echo (int) $e['minutes']; ?>:00</b> min.</span>
      </div>
      <div class="eh-card-foot"><span>👤 <?php echo esc_html(number_format_i18n($attempts)); ?></span><span class="go">Start →</span></div>
    </a>
    <?php return ob_get_clean();
}

function eh_tg_buttons($class = '') {
    return '<div class="eh-tg ' . esc_attr($class) . '">'
        . '<a class="btn tg" href="' . esc_url(eh_opt('tg_channel')) . '" target="_blank" rel="noopener">📢 Telegram Channel</a>'
        . '<a class="btn tg alt" href="' . esc_url(eh_opt('tg_group')) . '" target="_blank" rel="noopener">💬 Telegram Group</a></div>';
}

/* default menu if none set */
function eh_member_links() {
    return [
        'live' => ['/live-typing/', '⚡ Live Typing'], 'exams' => ['/exams/', 'All Exams Typing'], 'history' => ['/my-dashboard/', 'Typing History'],
        'hall' => ['/exam-hall-typing/', 'Exam Hall Typing'], 'download' => ['/download-passages/', 'Download Passages'], 'insight' => ['/typing-insight/', '📈 Typing Insight'],
    ];
}
function eh_member_nav($active = '') {
    if (is_user_logged_in()) return ''; /* logged-in users already have these links in the header, like the exam site */
    $h = '<nav class="eh-mnav"><div class="wrap">';
    foreach (eh_member_links() as $k => $l) $h .= '<a class="' . ($k === $active ? 'on' : '') . ($k === 'insight' ? ' ins' : '') . '" href="' . esc_url(home_url($l[0])) . '">' . esc_html($l[1]) . '</a>';
    return $h . '</div></nav>';
}
function eh_default_menu() {
    $items = is_user_logged_in()
        ? array_values(eh_member_links())
        : [['/', 'Home'], ['/exams/', 'Exams'], ['/demo-typing/', 'Demo Typing'], ['/live-typing/', 'Live Typing'], ['/exam-based-result/', 'Result'], ['/blog/', 'Typing Tips']];
    echo '<ul class="eh-menu">';
    foreach ($items as $i) echo '<li><a href="' . esc_url(home_url($i[0])) . '">' . esc_html($i[1]) . '</a></li>';
    echo '</ul>';
}

/* login redirect: normal users go to dashboard */
add_filter('login_redirect', function ($to, $req, $user) {
    if ($user instanceof WP_User && !user_can($user, 'edit_posts')) return home_url('/my-dashboard/');
    return $to;
}, 10, 3);
add_filter('registration_redirect', function () { return wp_login_url(home_url('/my-dashboard/')); });

/* login page branding */
add_action('login_enqueue_scripts', function () {
    echo '<style>body.login{background:#f6f3ec;font-family:Inter,sans-serif}.login h1 a{background:none!important;text-indent:0!important;width:auto!important;height:auto!important;font-size:24px;font-weight:800;color:#14161a}.login #nav a,.login #backtoblog a{color:#14161a}.wp-core-ui .button-primary{background:#111;border-color:#111;border-radius:10px}</style>';
});
add_filter('login_headerurl', function () { return home_url('/'); });
add_filter('login_headertext', function () { return eh_opt('brand'); });

function eh_initials($name) {
    $parts = preg_split('/\s+/u', trim((string) $name));
    $i = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1));
    if (count($parts) > 1) $i .= mb_strtoupper(mb_substr(end($parts), 0, 1));
    return $i;
}

/* Top ticker: notice + latest typing-tips posts */
function eh_ticker_items() {
    $items = [];
    $n = eh_opt('notice'); if ($n) $items[] = [$n, home_url('/live-typing/'), 'Live Typing →'];
    $ps = get_posts(['numberposts' => 10, 'category_name' => 'typing-tips', 'post_status' => 'publish']);
    foreach ($ps as $p) $items[] = [strtok(get_the_title($p), ':') . ' — Typing Rules', get_permalink($p), 'Read Blog'];
    return $items;
}

/* Upcoming exams popup data (option eh_upcoming JSON: {"confirmed":[...],"next":[...]}) */
function eh_upcoming() {
    $j = json_decode((string) get_option('eh_upcoming', ''), true);
    $j = is_array($j) ? $j : [];
    $j += ['confirmed' => [], 'next' => [
        ['tag' => 'LIVE', 'name' => 'Daily Live Typing', 'sub' => 'Live tests for many exams every day, with ranking', 'status' => 'Live Typing Active', 'url' => '/live-typing/'],
        ['tag' => 'HALL', 'name' => 'Exam Hall Typing', 'sub' => 'Morning, afternoon and evening: a new passage at a fixed time', 'status' => '3 times a day', 'url' => '/exam-hall-typing/'],
        ['tag' => 'PDF', 'name' => 'Passage PDF Download', 'sub' => 'English and Hindi passages by date', 'status' => 'Free', 'url' => '/download-passages/'],
        ['tag' => 'COURT', 'name' => 'Special Passages', 'sub' => 'Court orders, bail orders, office orders and more', 'status' => 'Ready for Practice', 'url' => '/exams/?q=Court'],
    ]];
    return $j;
}
