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
        for ($k = 0; $k < 6 && $n; $k++) $items[] = $pool[(($day * 6 + $k) % $n + $n) % $n];
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

/* Live Typing. Default: 30 UPSSSC Junior Assistant tests a day (15 English + 15 Hindi), all open the whole day;
   the set of passages moves on every day. Option eh_live_schedule (JSON [{"exam":"slug","start":"07:00","end":"10:40"}])
   replaces this list with timed slots when it is filled in. */
function eh_live_schedule() {
    $date = wp_date('Y-m-d');
    $j = json_decode((string) get_option('eh_live_schedule', ''), true);
    $out = [];
    if (!is_array($j) || !$j) {
        /* 6 English + 6 Hindi a day: 2 Easy, 2 Moderate, 2 Hard in each language. Every test is a full-length text:
           the day's passage first, then more passages of the same level, so nobody runs out of words. */
        $day = (int) floor(current_time('timestamp') / 86400);
        $groups = ['Easy' => ['B', 'E'], 'Moderate' => ['M'], 'Hard' => ['H']];
        foreach ([['en', 'upsssc-assistants-english-typing-test', 'English'], ['hi', 'upsssc-assistants-hindi-typing-test', 'Hindi']] as $x) {
            $e = eh_exam($x[1]); if (!$e) continue;
            $e['name'] = 'UPSSSC Junior Assistant ' . $x[2] . ' Typing';
            $all = array_values(array_filter(eh_passages($x[0]), function ($p) { return empty($p['special']) && strpos($p['id'], 'p') !== 0; }));
            $need = max((int) ceil($e['target'] * 1.3), (int) $e['minutes'] * 720); /* enough text for well over 100 WPM */
            $no = 0;
            foreach ($groups as $label => $lv) {
                $pool = array_values(array_filter($all, function ($p) use ($lv) { return in_array($p['level'], $lv, true); }));
                $n = count($pool); if (!$n) continue;
                for ($k = 0; $k < 2 && $k < $n; $k++) {
                    $start = ($day * 2 + $k) % $n; $seq = []; $len = 0;
                    for ($q = 0; $q < $n && $q < 8; $q++) { $seq[] = $pool[($start + $q) % $n]; $len += $seq[$q]['chars'] + 1; if ($len >= $need && $q >= 1) break; }
                    $out[] = ['exam' => $e, 'lang' => $x[0], 'passage' => $seq[0], 'plist' => $seq, 'chars' => max(0, $len - 1), 'level' => $label, 'no' => ++$no, 'allday' => true,
                        'start' => '12:00 AM', 'end' => '11:59 PM', 's' => 0, 'e' => 1439, 'date' => $date];
                }
            }
        }
        return $out;
    }
    foreach ($j as $r) {
        $e = eh_exam($r['exam'] ?? ''); if (!$e) continue;
        $lang = $e['lang'] === 'hi' ? 'hi' : 'en';
        $pool = array_values(array_filter(eh_passages($lang), function ($p) { return empty($p['special']); }));
        $p = $pool ? $pool[abs(crc32($date . $e['slug'])) % count($pool)] : null;
        $toMin = function ($t) { $x = explode(':', (string) $t); return ((int) $x[0]) * 60 + (int) ($x[1] ?? 0); };
        $out[] = ['exam' => $e, 'lang' => $lang, 'passage' => $p, 'start' => $r['start'], 'end' => $r['end'], 's' => $toMin($r['start']), 'e' => $toMin($r['end']), 'date' => $date];
    }
    return $out;
}
