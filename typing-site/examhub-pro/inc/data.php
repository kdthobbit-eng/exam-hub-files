<?php
if (!defined('ABSPATH')) exit;

/* Exams: theme JSON + admin overrides (option eh_exams_extra = JSON array) */
function eh_exams() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $list = json_decode(file_get_contents(EH_DIR . '/data/exams.json'), true) ?: [];
    $extra = json_decode((string) get_option('eh_exams_extra', '[]'), true);
    if (is_array($extra)) {
        $bySlug = [];
        foreach ($list as $i => $e) $bySlug[$e['slug']] = $i;
        foreach ($extra as $e) {
            if (empty($e['slug'])) continue;
            if (isset($bySlug[$e['slug']])) $list[$bySlug[$e['slug']]] = array_merge($list[$bySlug[$e['slug']]], $e);
            else array_unshift($list, $e);
        }
    }
    $hidden = array_filter(array_map('trim', explode(',', (string) get_option('eh_exams_hidden', ''))));
    $list = array_values(array_filter($list, function ($e) use ($hidden) { return !in_array($e['slug'], $hidden, true); }));
    $allowed = (float) eh_opt('allowed_error');
    foreach ($list as &$e) {
        $e += ['lang' => 'en', 'unit' => 'words', 'target' => 300, 'minutes' => 10, 'cat' => 'other', 'retype' => false, 'rule' => 'words'];
        if (!isset($e['allowed'])) $e['allowed'] = $allowed;
    }
    return $cache = $list;
}

function eh_exam($slug) {
    foreach (eh_exams() as $e) if ($e['slug'] === $slug) return $e;
    return null;
}

/* Passages: theme JSON + "Typing Passage" posts from WP admin */
add_action('init', function () {
    register_post_type('eh_passage', [
        'label' => 'Typing Passages',
        'labels' => ['name' => 'Typing Passages', 'singular_name' => 'Typing Passage', 'add_new_item' => 'नया पैसेज जोड़ें'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-editor-paste-text',
        'supports' => ['title', 'editor'],
    ]);
    register_taxonomy('eh_plang', 'eh_passage', ['label' => 'Language (en / hi)', 'public' => false, 'show_ui' => true, 'show_admin_column' => true, 'hierarchical' => true, 'show_in_rest' => true]);
});

function eh_passages($lang = null) {
    $list = json_decode(file_get_contents(EH_DIR . '/data/passages.json'), true) ?: [];
    $posts = get_posts(['post_type' => 'eh_passage', 'post_status' => 'publish', 'numberposts' => 300]);
    foreach ($posts as $p) {
        $terms = wp_get_post_terms($p->ID, 'eh_plang', ['fields' => 'slugs']);
        $txt = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($p->post_content)));
        $pl = $terms ? $terms[0] : (preg_match('/[\x{0900}-\x{097F}]/u', $txt) ? 'hi' : 'en');
        if ($txt === '') continue;
        array_unshift($list, ['id' => 'p' . $p->ID, 'lang' => $pl, 'title' => $p->post_title, 'text' => $txt,
            'words' => count(preg_split('/\s+/u', $txt)), 'chars' => mb_strlen($txt)]);
    }
    if ($lang) $list = array_values(array_filter($list, function ($p) use ($lang) { return $p['lang'] === $lang; }));
    return $list;
}

/* Daily live passage — same for everyone today */
function eh_live_info() {
    $date = wp_date('Y-m-d');
    $lang = ((int) wp_date('j')) % 2 ? 'en' : 'hi';
    $override = get_option('eh_live_lang', '');
    if ($override === 'en' || $override === 'hi') $lang = $override;
    $ps = eh_passages($lang);
    $idx = $ps ? (abs(crc32($date)) % count($ps)) : 0;
    return ['date' => $date, 'lang' => $lang, 'passage' => $ps[$idx]['id'] ?? '', 'minutes' => 10];
}
