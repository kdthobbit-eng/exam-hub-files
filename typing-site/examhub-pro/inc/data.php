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
    register_taxonomy('eh_ptype', 'eh_passage', ['label' => 'Passage type (special)', 'public' => false, 'show_ui' => true, 'show_admin_column' => true, 'hierarchical' => true, 'show_in_rest' => true]);
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
        $types = wp_get_post_terms($p->ID, 'eh_ptype', ['fields' => 'slugs']);
        array_unshift($list, ['id' => 'p' . $p->ID, 'lang' => $pl, 'title' => $p->post_title, 'text' => $txt,
            'words' => count(preg_split('/\s+/u', $txt)), 'chars' => mb_strlen($txt),
            'special' => in_array('special', (array) $types, true), 'date' => get_the_date('Y-m-d', $p)]);
    }
    if ($lang) $list = array_values(array_filter($list, function ($p) use ($lang) { return $p['lang'] === $lang; }));
    return eh_passage_levels($list);
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

/* Difficulty B/E/M/H — word length + punctuation/digits, ranked inside each language */
function eh_passage_levels($list) {
    $by = [];
    foreach ($list as $i => $p) {
        $w = max(1, $p['words'] ?? 1);
        $sym = preg_match_all('/[0-9,.;:()"\'\-।]/u', $p['text']);
        $by[$p['lang']][$i] = ($p['chars'] / $w) + 4 * ($sym / $w);
        $list[$i] += ['special' => false, 'date' => ''];
    }
    foreach ($by as $scores) {
        asort($scores); $n = count($scores); $k = 0;
        foreach ($scores as $i => $sc) { $list[$i]['level'] = ['B', 'E', 'M', 'H'][min(3, (int) floor(4 * $k / max(1, $n)))]; $k++; }
    }
    return $list;
}

/* Passages released per day: 3 per day (morning / afternoon / evening) by rotation + admin passages on their own date */
function eh_passage_days($lang, $days = 7) {
    $all = eh_passages($lang);
    $pool = array_values(array_filter($all, function ($p) { return empty($p['special']) && strpos($p['id'], 'p') !== 0; }));
    $n = count($pool); $out = [];
    $today = (int) floor((current_time('timestamp')) / 86400);
    for ($d = 0; $d < $days; $d++) {
        $day = $today - $d; $date = gmdate('Y-m-d', $day * 86400); $items = [];
        foreach ($all as $p) if (strpos($p['id'], 'p') === 0 && empty($p['special']) && $p['date'] === $date) $items[] = $p;
        for ($k = 0; $k < 3 && $n; $k++) $items[] = $pool[(($day * 3 + $k) % $n + $n) % $n];
        $out[] = ['date' => $date, 'items' => $items];
    }
    return $out;
}

/* Today's slot passage (exam hall): slot 0 morning, 1 afternoon, 2 evening */
function eh_slot_passage($lang, $slot) {
    $d = eh_passage_days($lang, 1);
    $items = $d[0]['items'];
    if (!$items) return null;
    $rot = array_slice($items, -3);
    return $rot[$slot] ?? $items[0];
}
