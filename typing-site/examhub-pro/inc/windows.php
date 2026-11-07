<?php
/**
 * Exam-specific typing windows.
 * Each exam slug maps to a "family"; each family has a layout + sizes.
 * Override per exam from admin/chat with option eh_window_map (JSON {"slug":"family"}).
 */
if (!defined('ABSPATH')) exit;

/**
 * Per-family, per-language metrics measured on the real exam screens (viewport 1366px).
 * row: layout | passage h | typing h | passage font | passage size/line | typing size/line | typing bg
 * fonts: M Mangal, S serif, A Arial, T Times New Roman, C Calibri, I Inter
 * layouts: a awide jssc rssb b c ntpc sidea sidec nopass nokbc nokbb
 */
function eh_window_table() {
    $hi = [
        'agniveer' => 'a|187|140|M|16/26|22/28', 'airforce' => 'a|187|140|M|16/26|22/28', 'aphc' => 'a|187|140|M|16/26|22/28',
        'bcc' => 'a|187|140|M|16/26|22/28', 'beltron' => 'a|187|140|M|16/26|22/28', 'bssc' => 'a|187|140|M|16/26|22/28',
        'dda' => 'a|187|140|M|16/26|22/28', 'djb' => 'a|187|140|M|16/26|22/28', 'dsssb' => 'a|187|140|M|16/26|22/28',
        'mpcourt' => 'a|187|140|M|16/26|22/28', 'mpsc' => 'a|187|140|M|16/26|22/28', 'sci' => 'a|187|140|M|16/26|22/28',
        'bhu' => 'a|187|168|S|16/26|19/24', 'mpcpct' => 'a|187|168|S|16/26|19/24', 'nvs' => 'a|187|168|S|16/26|19/24',
        'bpssc' => 'awide|187|140|M|16/26|22/28', 'hcraj' => 'awide|187|140|M|16/26|22/28', 'jssc' => 'jssc|187|140|M|16/26|22/28',
        'rssb' => 'rssb|187|140|M|16/26|22/28',
        'csir' => 'b|187|140|M|16/26|22/28', 'dhc' => 'b|187|140|M|16/26|22/28', 'ssc' => 'b|187|140|M|16/26|22/28',
        'jeevika' => 'b|187|140|M|17/26|22/28', 'spmcil' => 'b|253|168|S|18/28|19/24',
        'bvss' => 'c|187|140|M|16/26|22/28', 'cghc' => 'c|187|140|M|16/26|22/28', 'curec' => 'c|187|140|M|16/26|22/28',
        'jhc' => 'c|187|140|M|16/26|22/28', 'tshc' => 'c|187|140|M|16/26|22/28', 'ukhcrec' => 'c|187|140|M|16/26|22/28',
        'ukpsc' => 'c|187|140|M|16/26|22/28', 'ukpsc-ro-aro' => 'c|187|140|M|16/26|22/28', 'visva-bharati' => 'c|187|140|M|16/26|22/28',
        'wbpsc' => 'c|187|140|M|16/26|22/28',
        'ahc' => 'c|253|168|S|18/28|19/24', 'jnu' => 'c|253|168|S|18/28|19/24', 'mp-police' => 'c|253|168|S|18/28|19/24',
        'cbse' => 'c|295|168|S|18/26|22/24', 'kvs' => 'c|295|168|S|18/26|22/24', 'emrs' => 'c|270|168|S|18/26|22/24',
        'uppcl' => 'c|230|140|S|18/26|19/24', 'upsssc' => 'c|230|140|S|18/26|19/24|ccff66',
        'uppsc' => 'c|160|140|S|18/26|20/28', 'uppsc-ro-aro' => 'c|145|140|S|18/26|20/28',
        'bhc' => 'sidec|187|187|M|16/26|22/28', 'jci' => 'sidea|187|187|M|16/26|22/28', 'uksssc' => 'sidea|387|387|M|16/26|22/28',
        'delhi-hcm' => 'nopass|0|300|M|16/26|22/28', 'phhc' => 'nopass|0|300|M|16/26|22/28', 'hcm' => 'nopass|0|300|M|16/26|22/28', 'ssb' => 'nopass|0|300|M|16/26|22/28',
        'aiims' => 'nokbc|187|140|M|16/26|22/28', 'up-police' => 'nokbc|253|168|S|20/28|20/24|ccff66', 'ossc' => 'nokbb|187|140|M|16/26|22/28',
        'ntpc' => 'ntpc|187|140|S|16/26|22/28',
    ];
    $en = [
        'agniveer' => 'a|187|140|A|19/30|21/28', 'airforce' => 'a|187|140|A|19/30|21/28', 'bcc' => 'a|187|140|A|19/30|21/28',
        'bhu' => 'a|187|140|A|19/30|21/28', 'bssc' => 'a|187|140|A|19/30|21/28', 'djb' => 'a|187|140|A|19/30|21/28',
        'dsssb' => 'a|187|140|A|19/30|21/28', 'mpcourt' => 'a|187|140|A|19/30|21/28', 'mpcpct' => 'a|187|140|A|19/30|21/28',
        'mpsc' => 'a|187|140|A|19/30|21/28', 'nvs' => 'a|187|140|A|19/30|21/28', 'sci' => 'a|187|140|A|19/30|21/28',
        'beltron' => 'a|187|140|T|19/30|20/27', 'dda' => 'a|187|140|T|19/30|21/28',
        'bpssc' => 'awide|174|140|A|19/21|20.5/23', 'hcraj' => 'awide|193|140|C|18/19|19/21', 'jssc' => 'jssc|187|140|A|19/30|21/28',
        'rssb' => 'rssb|254|125|C|20/25|21/25',
        'aphc' => 'b|240|140|T|20/24|21/24', 'dhc' => 'b|240|140|T|20/24|21/24', 'csir' => 'b|270|140|T|18/24|20/24',
        'jeevika' => 'b|247|140|T|19/24|20/24', 'spmcil' => 'b|222|168|T|20/24|21/24', 'ssc' => 'b|270|140|T|19/30|21/24',
        'ahc' => 'c|270|154|T|20/22|21/24', 'jnu' => 'c|270|154|T|20/22|21/24', 'bvss' => 'c|270|140|T|20/24|21/24',
        'cbse' => 'c|295|140|T|19/24|20/24', 'kvs' => 'c|295|140|T|19/24|20/24', 'emrs' => 'c|270|140|T|19/24|20/24',
        'curec' => 'c|290|140|T|19/24|20/24', 'ukhcrec' => 'c|290|140|T|19/24|20/24', 'ukpsc' => 'c|290|140|T|19/24|20/24',
        'ukpsc-ro-aro' => 'c|290|140|T|19/24|20/24', 'visva-bharati' => 'c|290|140|T|19/24|20/24',
        'cghc' => 'c|253|168|T|18/28|18/28', 'wbpsc' => 'c|253|168|T|18/28|18/28', 'jhc' => 'c|230|140|T|20/24|21/24',
        'tshc' => 'c|222|140|T|20/24|21/24', 'mp-police' => 'c|187|140|A|19/30|21/28',
        'uppcl' => 'c|245|167|T|19/24|20/24', 'upsssc' => 'c|245|167|T|19/24|20/24|ccff66',
        'uppsc' => 'c|150|140|T|19/24|20/24', 'uppsc-ro-aro' => 'c|145|140|T|19/24|20/24',
        'bhc' => 'sidec|365|365|T|20/24|21/24', 'jci' => 'sidea|320|320|I|19/24|19/24', 'uksssc' => 'sidea|387|387|T|19/24|20/24',
        'delhi-hcm' => 'nopass|0|300|T|18/27|20/24', 'hcm' => 'nopass|0|300|T|18/27|20/24', 'ssb' => 'nopass|0|300|T|18/27|20/24', 'phhc' => 'nopass|0|300|T|18/27|21/24',
        'aiims' => 'nokbc|385|154|T|18/22|18/22|c4ff4d', 'up-police' => 'nokbc|385|154|T|18/22|18/22|c4ff4d', 'ossc' => 'nokbb|235|140|T|17/18|18/20|aed75a',
        'ntpc' => 'ntpc|187|140|A|19/30|21/28',
    ];
    return ['hi' => $hi, 'en' => $en];
}

/* families whose current word is shown with a yellow background (and blue text) */
function eh_window_hl($fam) {
    if (in_array($fam, ['agniveer', 'airforce', 'beltron', 'bhu', 'bssc', 'dda', 'mpcpct', 'mpsc', 'nvs', 'rssb', 'uksssc', 'uppsc-ro-aro', 'wbpsc', 'tcs'], true)) return 'y';
    if ($fam === 'upsssc') return 'b';
    return 'n';
}

function eh_window_families() {
    $F = [];
    $tool = ['bcc', 'mpcourt', 'rssb', 'cghc', 'jhc', 'ahc', 'jnu', 'mp-police', 'emrs', 'upsssc', 'uppcl', 'hcm', 'ssb', 'aiims', 'up-police', 'ntpc', 'tcs'];
    $stats = ['agniveer', 'airforce', 'beltron', 'bssc', 'dda', 'mpsc', 'bcc', 'mpcourt', 'bhu', 'mpcpct', 'nvs', 'bpssc', 'hcraj', 'jssc', 'jci', 'uksssc', 'ntpc', 'tcs'];
    $T = eh_window_table();
    foreach (array_keys($T['hi']) as $f) $F[$f] = ['layout' => explode('|', $T['hi'][$f])[0], 'stats' => in_array($f, $stats, true) ? 1 : 0, 'tool' => in_array($f, $tool, true) ? 1 : 0];
    $F['chsl'] = $F['jkssb'] = $F['ssc'];
    $F['tcs'] = ['layout' => 'a', 'stats' => 1, 'tool' => 1];
    return $F;
}

/* resolve sizes for a family + language */
function eh_window_metrics($fam, $lang) {
    $T = eh_window_table();
    $k = in_array($fam, ['chsl', 'jkssb'], true) ? 'ssc' : ($fam === 'tcs' ? 'dsssb' : $fam);
    $row = $lang === 'en' ? ($T['en'][$k] ?? $T['en']['ssc']) : ($T['hi'][$k] ?? $T['hi']['ssc']);
    $x = explode('|', $row);
    list($pfs, $plh) = explode('/', $x[4]); list($tfs, $tlh) = explode('/', $x[5]);
    $ff = ['M' => '"Mangal","Noto Sans Devanagari",sans-serif', 'S' => '"Times New Roman","Noto Serif Devanagari",serif', 'A' => 'Arial,Helvetica,sans-serif',
           'T' => '"Times New Roman",Times,serif', 'C' => 'Calibri,Carlito,"Segoe UI",sans-serif', 'I' => 'Inter,Arial,sans-serif'][$x[3]] ?? 'Arial,sans-serif';
    return ['layout' => $x[0], 'ph' => (int) $x[1], 'th' => (int) $x[2], 'ff' => $ff, 'pfs' => (float) $pfs, 'plh' => (float) $plh, 'tfs' => (float) $tfs, 'tlh' => (float) $tlh, 'tbg' => $x[6] ?? '', 'pbg' => ($lang !== 'en' && in_array($k, ['upsssc', 'uppcl'], true)) ? 'fafafa' : ''];
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

function eh_window($slug, $lang = '') {
    $F = eh_window_families();
    $over = json_decode((string) get_option('eh_window_map', ''), true);
    $fam = '';
    if (is_array($over) && !empty($over[$slug]) && isset($F[$over[$slug]])) $fam = $over[$slug];
    if (!$fam && $slug === 'demo') $fam = 'tcs';
    if (!$fam) foreach (eh_window_keys() as $k => $f) { if (strpos($slug, $k) !== false) { $fam = $f; break; } }
    if (!$fam || !isset($F[$fam])) $fam = 'ssc';
    if (!$lang) { $e = function_exists('eh_exam') ? eh_exam($slug) : null; $lang = $e && $e['lang'] === 'en' ? 'en' : 'hi'; }
    $m = eh_window_metrics($fam, $lang);
    return ['family' => $fam, 'hl' => eh_window_hl($fam), 'serif' => 0, 'lime' => 0] + $m + $F[$fam];
}

function eh_window_classes($w) {
    $c = ['ew-' . $w['layout'], 'ew-hl-' . $w['hl']];
    foreach (['stats', 'tool'] as $k) if (!empty($w[$k])) $c[] = 'ew-' . $k;
    return implode(' ', $c);
}

function eh_window_style($w) {
    $v = ['--ph:' . (int) $w['ph'] . 'px', '--th:' . (int) $w['th'] . 'px', '--pff:' . $w['ff'], '--pfs:' . $w['pfs'] . 'px', '--plh:' . $w['plh'] . 'px', '--tfs:' . $w['tfs'] . 'px', '--tlh:' . $w['tlh'] . 'px'];
    if ($w['tbg']) $v[] = '--tbg:#' . $w['tbg'];
    if ($w['pbg']) $v[] = '--pbg:#' . $w['pbg'];
    return implode(';', $v);
}
