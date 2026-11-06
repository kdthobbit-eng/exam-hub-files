<?php
/**
 * Exam-specific typing windows.
 * Each exam slug maps to a "family"; each family has a layout + sizes.
 * Override per exam from admin/chat with option eh_window_map (JSON {"slug":"family"}).
 */
if (!defined('ABSPATH')) exit;

/** layout: a | awide | jssc | rssb | b | c | ntpc | sidea | sidec | nopass | nokbc | nokbb
 *  ph = passage height, th = typing box height, serif, stats, tool, pbg (grey passage), lime (green typing box) */
function eh_window_families() {
    $F = [];
    $def = ['layout' => 'a', 'ph' => 187, 'th' => 140, 'serif' => 0, 'stats' => 0, 'tool' => 0, 'pbg' => 0, 'lime' => 0];
    $set = function ($names, $o) use (&$F, $def) { foreach ((array) $names as $n) $F[$n] = array_merge($def, $o); };
    $set(['aphc', 'sci', 'djb', 'dsssb'], []);
    $set(['agniveer', 'airforce', 'beltron', 'bssc', 'dda', 'mpsc'], ['stats' => 1]);
    $set(['bcc', 'mpcourt'], ['stats' => 1, 'tool' => 1]);
    $set(['bhu', 'mpcpct', 'nvs'], ['serif' => 1, 'th' => 168, 'stats' => 1]);
    $set(['bpssc', 'hcraj'], ['layout' => 'awide', 'stats' => 1]);
    $set('jssc', ['layout' => 'jssc', 'stats' => 1]);
    $set('rssb', ['layout' => 'rssb', 'tool' => 1]);
    $set(['csir', 'dhc', 'ssc', 'jeevika', 'chsl', 'jkssb'], ['layout' => 'b']);
    $set('spmcil', ['layout' => 'b', 'serif' => 1, 'ph' => 253, 'th' => 168]);
    $set(['bvss', 'curec', 'tshc', 'ukhcrec', 'ukpsc', 'ukpsc-ro-aro', 'visva-bharati', 'wbpsc'], ['layout' => 'c']);
    $set(['cghc', 'jhc'], ['layout' => 'c', 'tool' => 1]);
    $set(['cbse', 'kvs'], ['layout' => 'c', 'serif' => 1, 'ph' => 295, 'th' => 168]);
    $set(['ahc', 'jnu', 'mp-police'], ['layout' => 'c', 'serif' => 1, 'ph' => 253, 'th' => 168, 'tool' => 1]);
    $set('emrs', ['layout' => 'c', 'serif' => 1, 'ph' => 270, 'th' => 168, 'tool' => 1]);
    $set('uppsc', ['layout' => 'c', 'serif' => 1, 'ph' => 160]);
    $set('uppsc-ro-aro', ['layout' => 'c', 'serif' => 1, 'ph' => 145]);
    $set('upsssc', ['layout' => 'c', 'serif' => 1, 'ph' => 230, 'pbg' => 1, 'lime' => 1, 'tool' => 1]);
    $set('uppcl', ['layout' => 'c', 'serif' => 1, 'ph' => 230, 'pbg' => 1, 'tool' => 1]);
    $set('bhc', ['layout' => 'sidec', 'th' => 187]);
    $set('jci', ['layout' => 'sidea', 'th' => 187, 'stats' => 1]);
    $set('uksssc', ['layout' => 'sidea', 'ph' => 387, 'th' => 387, 'stats' => 1]);
    $set(['delhi-hcm', 'phhc'], ['layout' => 'nopass', 'th' => 300]);
    $set(['hcm', 'ssb'], ['layout' => 'nopass', 'th' => 300, 'tool' => 1]);
    $set('aiims', ['layout' => 'nokbc', 'tool' => 1]);
    $set('ossc', ['layout' => 'nokbb']);
    $set('up-police', ['layout' => 'nokbc', 'serif' => 1, 'ph' => 253, 'th' => 168, 'lime' => 1, 'tool' => 1]);
    $set('ntpc', ['layout' => 'ntpc', 'serif' => 1, 'stats' => 1, 'tool' => 1]);
    $set('tcs', ['stats' => 1, 'tool' => 1]); // demo / generic
    return $F;
}

/** Keyword → family. Order matters: first keyword found in the slug wins. */
function eh_window_keys() {
    return [
        'agniveer' => 'agniveer', 'aiims' => 'aiims', 'allahabad' => 'ahc', 'aphc' => 'aphc', 'beltron' => 'beltron',
        'bhu' => 'bhu', 'bombay' => 'bhc', 'bpssc' => 'bpssc', 'bsf' => 'hcm', 'wbpsc' => 'wbpsc', 'bssc' => 'bssc',
        'cbse' => 'cbse', 'cghc' => 'cghc', 'csir' => 'csir', 'dhc' => 'dhc', 'emrs' => 'emrs', 'vidhan' => 'bvss',
        'dda' => 'dda', 'dsssb' => 'dsssb', 'hcraj' => 'hcraj', 'jabalpur' => 'mpcourt', 'jeevika' => 'jeevika',
        'jhc' => 'jhc', 'jkssb' => 'jkssb', 'jnu' => 'jnu', 'jssc' => 'jssc', 'jute' => 'jci', 'kvs' => 'kvs',
        'marathon' => 'upsssc', 'mpsc' => 'mpsc', 'navoday' => 'cbse', 'nta' => 'curec', 'ntpc' => 'ntpc', 'nvs' => 'nvs',
        'odisha' => 'ossc', 'rssb' => 'rssb', 'uksssc' => 'uksssc', 'uppcl' => 'uppcl', 'upsssc' => 'upsssc',
        'spmcil' => 'spmcil', 'ssc' => 'ssc', 'supreme' => 'sci', 'telangana' => 'tshc', 'ukhcrec' => 'ukhcrec',
        'bihar-civil-court' => 'bcc', 'mp-cpct' => 'mpcpct', 'mp-police' => 'mp-police', 'delhi-police-hcm' => 'delhi-hcm',
        'patna-high-court' => 'bcc', 'ssb-hcm' => 'ssb', 'ukpsc-aps' => 'ukpsc', 'ukpsc-ro-aro' => 'uksssc',
        'up-police' => 'up-police', 'uppsc-ro-aro' => 'uppsc-ro-aro', 'punjab-haryana-high-court' => 'phhc',
        // fall-backs for slugs no keyword above catches
        'uppsc' => 'uppsc', 'air-force' => 'airforce', 'delhi-jal-board' => 'djb', 'uttarakhand-high-court' => 'ukhcrec',
    ];
}

function eh_window($slug) {
    $F = eh_window_families();
    $over = json_decode((string) get_option('eh_window_map', ''), true);
    $fam = '';
    if (is_array($over) && !empty($over[$slug]) && isset($F[$over[$slug]])) $fam = $over[$slug];
    if (!$fam && $slug === 'demo') $fam = 'tcs';
    if (!$fam) foreach (eh_window_keys() as $k => $f) { if (strpos($slug, $k) !== false) { $fam = $f; break; } }
    if (!$fam || !isset($F[$fam])) $fam = 'ssc';
    return ['family' => $fam] + $F[$fam];
}

function eh_window_classes($w) {
    $c = ['ew-' . $w['layout']];
    foreach (['serif', 'stats', 'tool', 'pbg', 'lime'] as $k) if (!empty($w[$k])) $c[] = 'ew-' . $k;
    return implode(' ', $c);
}
