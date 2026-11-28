<?php
/* UP TGT / PGT subject-wise live mocks. Each subject is its own round (tg01…, pg01…) on the same engine as Super TET:
   paper = 30 common General Studies questions + 90 subject questions, 120 Q, 360 marks, +3/−1, 120 minutes. */
if (!defined('ABSPATH')) exit;

/* window for all TGT/PGT mocks (IST) — change here later */
define('EH_TP_LOGIN', '2026-11-27 00:00');
define('EH_TP_END', '2026-12-31 23:59');

function eh_tp_subjects() {
    return [
        'tgt' => [
            'hindi' => ['हिंदी', 'Hindi'], 'english' => ['अंग्रेज़ी', 'English'], 'maths' => ['गणित', 'Mathematics'], 'science' => ['विज्ञान', 'Science'],
            'social-science' => ['सामाजिक विज्ञान', 'Social Science'], 'sanskrit' => ['संस्कृत', 'Sanskrit'], 'home-science' => ['गृह विज्ञान', 'Home Science'],
            'phys-edu' => ['शारीरिक शिक्षा', 'Physical Education'], 'art' => ['कला (चित्रकला)', 'Art'], 'biology' => ['जीव विज्ञान', 'Biology'],
            'agriculture' => ['कृषि', 'Agriculture'], 'commerce' => ['वाणिज्य', 'Commerce'], 'urdu' => ['उर्दू', 'Urdu'], 'music-vocal' => ['संगीत गायन', 'Music (Vocal)'],
            'ranjan-kala' => ['रंजन कला', 'Ranjan Kala'], 'music-inst' => ['संगीत वादन', 'Music (Instrumental)'], 'sewing' => ['सिलाई', 'Sewing'],
        ],
        'pgt' => [
            'hindi' => ['हिंदी', 'Hindi'], 'english' => ['अंग्रेज़ी', 'English'], 'sanskrit' => ['संस्कृत', 'Sanskrit'], 'geography' => ['भूगोल', 'Geography'],
            'economics' => ['अर्थशास्त्र', 'Economics'], 'chemistry' => ['रसायन विज्ञान', 'Chemistry'], 'physics' => ['भौतिक विज्ञान', 'Physics'], 'civics' => ['नागरिक शास्त्र', 'Civics'],
            'biology' => ['जीव विज्ञान', 'Biology'], 'maths' => ['गणित', 'Mathematics'], 'history' => ['इतिहास', 'History'], 'sociology' => ['समाजशास्त्र', 'Sociology'],
            'craft' => ['क्राफ्ट', 'Craft'], 'commerce' => ['वाणिज्य', 'Commerce'], 'psychology' => ['मनोविज्ञान', 'Psychology'], 'education' => ['शिक्षाशास्त्र', 'Education'],
            'agriculture' => ['कृषि', 'Agriculture'], 'phys-edu' => ['शारीरिक शिक्षा', 'Physical Education'], 'urdu' => ['उर्दू', 'Urdu'], 'home-science' => ['गृह विज्ञान', 'Home Science'],
            'military' => ['सैन्य विज्ञान', 'Military Science'], 'logic' => ['तर्कशास्त्र', 'Logic'], 'music-inst' => ['संगीत वादन', 'Music (Instrumental)'],
            'music' => ['संगीत', 'Music'], 'agronomy' => ['शस्य विज्ञान', 'Agronomy'], 'ranjan-kala' => ['रंजन कला', 'Ranjan Kala'],
        ],
    ];
}
function eh_tp_info($s) {
    if ($s === 'gs') return ['short' => 'TGT/PGT', 'title' => 'UP TGT / PGT 2026', 'post' => 'सामान्य अध्ययन (TGT व PGT दोनों के लिए)', 'adv' => 'विज्ञापन 06/2026 व 07/2026', 'level' => 'सामान्य अध्ययन'];
    return $s === 'tgt'
        ? ['short' => 'TGT', 'title' => 'UP TGT 2026', 'post' => 'सहायक अध्यापक (प्रशिक्षित स्नातक श्रेणी)', 'adv' => 'विज्ञापन 07/2026', 'level' => 'कक्षा 6–10']
        : ['short' => 'PGT', 'title' => 'UP PGT 2026', 'post' => 'प्रवक्ता (स्नातकोत्तर श्रेणी)', 'adv' => 'विज्ञापन 06/2026', 'level' => 'कक्षा 11–12'];
}
/* all TGT/PGT rounds, in display order */
function eh_tp_rounds() {
    $out = [];
    foreach (eh_tp_subjects() as $s => $list) {
        $i = 0; $inf = eh_tp_info($s);
        foreach ($list as $slug => $nm) {
            $i++;
            $out[] = ['id' => ($s === 'tgt' ? 'tg' : 'pg') . str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'series' => $s, 'slug' => $slug, 'sub' => $nm[0], 'sub_en' => $nm[1],
                'name' => strtoupper($s) . ' ' . eh_tp_en($slug, $nm[1]) . ' - MOCK TEST 1', 'login' => EH_TP_LOGIN, 'start' => EH_TP_LOGIN, 'end' => EH_TP_END,
                'paper' => 'tp/' . $s . '-' . $slug, 'gs' => 'tp/gs', 'kind' => 'full', 'secs' => 7200, 'nq' => 120];
        }
    }
    return $out;
}
/* full mocks + sectional mocks (subject only, 90 Q / 90 min) + one common GS sectional (30 Q / 30 min) — all from the same papers */
function eh_tp_rounds_all() {
    $out = eh_tp_rounds();
    foreach (eh_tp_rounds() as $r) {
        $out[] = array_merge($r, ['id' => $r['id'] . 's', 'kind' => 'sec', 'gs' => '', 'secs' => 5400, 'nq' => 90, 'name' => strtoupper($r['series']) . ' ' . eh_tp_en($r['slug'], $r['sub_en']) . ' - SECTIONAL TEST 1']);
    }
    $T = eh_tp_topics();
    foreach (eh_tp_rounds() as $r) foreach (($T[$r['series']][$r['slug']] ?? []) as $i => $tn) {
        $f = 'tp/topic/' . $r['series'] . '-' . $r['slug'] . '-' . ($i + 1);
        if (!is_file(EH_DIR . '/data/' . $f . '.json')) continue;
        $out[] = array_merge($r, ['id' => $r['id'] . 't' . ($i + 1), 'kind' => 'topic', 'paper' => $f, 'gs' => '', 'secs' => 3000, 'nq' => 50, 'topic' => $tn[0], 'tno' => $i + 1,
            'name' => eh_tp_topic_title($r, $i + 1, $tn)]);
    }
    /* full-length mocks with their own 30 GS + 90 subject questions (data/tp/fl/{series}-{slug}-{n}[-gs].json) — they replace the old single mock */
    foreach (eh_tp_rounds() as $r) for ($n = 1; $n <= 20; $n++) {
        $f = 'tp/fl/' . $r['series'] . '-' . $r['slug'] . '-' . $n;
        if (!is_file(EH_DIR . '/data/' . $f . '.json')) break;
        $out[] = array_merge($r, ['id' => $r['id'] . 'f' . $n, 'kind' => 'full', 'paper' => $f, 'gs' => $f . '-gs', 'fno' => $n,
            'name' => strtoupper($r['series']) . ' ' . eh_tp_en($r['slug'], $r['sub_en']) . ' - FULL LENGTH MOCK ' . $n]);
    }
    /* GS topic tests + GS sectional: same questions for every subject, but a separate round per subject (tg03g1…, tg03x),
       so attempts, results and "continue" stay with the subject the candidate is preparing */
    foreach (eh_tp_rounds() as $r) {
        $pre = strtoupper($r['series']) . ' GS - ';
        foreach (eh_tp_gs_topics() as $i => $tn) {
            $f = 'tp/topic/gs-' . ($i + 1);
            if (!is_file(EH_DIR . '/data/' . $f . '.json')) continue;
            $out[] = array_merge($r, ['id' => $r['id'] . 'g' . ($i + 1), 'kind' => 'topic', 'gst' => 1, 'sub' => 'सामान्य अध्ययन', 'sub_en' => 'General Studies', 'topic' => $tn[0], 'tno' => $i + 1,
                'name' => $pre . 'TOPIC TEST ' . ($i + 1) . ' (' . $tn[1] . ')', 'paper' => $f, 'gs' => '', 'secs' => 3000, 'nq' => 50]);
        }
        $out[] = array_merge($r, ['id' => $r['id'] . 'x', 'kind' => 'gs', 'sub' => 'सामान्य अध्ययन', 'sub_en' => 'General Studies', 'name' => $pre . 'SECTIONAL TEST 1', 'paper' => 'tp/gs', 'gs' => '', 'secs' => 1800, 'nq' => 30]);
    }
    /* old common GS sectional — kept only so earlier attempts still open */
    $out[] = ['id' => 'gsx', 'series' => 'gs', 'slug' => 'gs', 'sub' => 'सामान्य अध्ययन', 'sub_en' => 'General Studies', 'name' => 'TGT/PGT GS - SECTIONAL TEST 1',
        'login' => EH_TP_LOGIN, 'start' => EH_TP_LOGIN, 'end' => EH_TP_END, 'paper' => 'tp/gs', 'gs' => '', 'kind' => 'gs', 'secs' => 1800, 'nq' => 30];
    return $out;
}
/* English test names, same in every language: TGT MATH - MOCK TEST 1 */
function eh_tp_en($slug, $en) { return $slug === 'maths' ? 'MATH' : strtoupper($en); }
function eh_tp_topic_title($r, $n, $tn) { return strtoupper($r['series']) . ' ' . eh_tp_en($r['slug'], $r['sub_en']) . ' - TOPIC TEST ' . $n . (!empty($tn[1]) ? ' (' . $tn[1] . ')' : ''); }
function eh_tp_gs_topics() {
    return [
        ['समसामयिक घटनाएँ (राज्य, राष्ट्रीय, अंतर्राष्ट्रीय एवं खेल)', 'Current Affairs'],
        ['भारतीय इतिहास एवं भारतीय राष्ट्रीय आंदोलन', 'Indian History & National Movement'],
        ['भारतीय संविधान एवं राजव्यवस्था', 'Indian Constitution & Polity'],
        ['शिक्षा व्यवस्था से जुड़े विधिक एवं संवैधानिक विकास', 'Legal & Constitutional Development in Education'],
        ['भारतीय अर्थव्यवस्था', 'Indian Economy'],
        ['भारत का सामान्य भूगोल एवं जनसंख्या', 'Indian Geography & Population'],
        ['उत्तर प्रदेश विशेष सामान्य ज्ञान', 'Uttar Pradesh Special GK'],
        ['सामान्य विज्ञान, प्रौद्योगिकी एवं पर्यावरण', 'General Science, Technology & Environment'],
        ['मानव स्वास्थ्य एवं स्वच्छता', 'Human Health & Hygiene'],
        ['कंप्यूटर, इंटरनेट, ICT एवं शैक्षिक प्रौद्योगिकी', 'Computer, Internet, ICT & EdTech'],
    ];
}
/* full-length mocks of a subject round (tg03f1…); empty when it still uses the single old mock */
function eh_tp_flts($r) { $o = []; foreach (eh_stm_rounds() as $x) if (($x['fno'] ?? 0) && strpos($x['id'], $r['id'] . 'f') === 0) $o[] = $x; return $o; }
function eh_tp_topics() { static $t = null; if ($t === null) $t = json_decode((string) @file_get_contents(EH_DIR . '/data/tp/topics.json'), true) ?: []; return $t; }
function eh_stm_secs($r) { return (int) ($r['secs'] ?? EH_STM_SECS); }
function eh_stm_nq($r) { return (int) ($r['nq'] ?? 120); }
function eh_tp_is($r) { return in_array($r['series'] ?? '', ['tgt', 'pgt', 'gs'], true); }
/* a subject paper is ready only when both its GS file and its subject file exist */
function eh_tp_ready($r) {
    return is_file(EH_DIR . '/data/' . $r['paper'] . '.json') && (empty($r['gs']) || is_file(EH_DIR . '/data/' . $r['gs'] . '.json'));
}
/* ?r=tg01 / pg05 → that subject round */
function eh_tp_req_round() {
    $id = sanitize_key($_GET['r'] ?? ($_REQUEST['eh_r'] ?? ''));
    if (!preg_match('/^((tg|pg)\d\d(s|x|t\d\d?|f\d\d?|g\d\d?)?|gsx|gst\d\d?|p\d)$/', $id)) return null;
    foreach (eh_stm_rounds() as $r) if ($r['id'] === $id) return $r;
    return null;
}
function eh_tp_url($r) { return add_query_arg('r', $r['id'], home_url('/super-tet-mock/')); }
/* on the login / signup form: subject dropdown, pre-selected from the mock the candidate came from; changing it changes where they land */
function eh_tp_auth_select() {
    $to = rawurldecode((string) ($_REQUEST['redirect_to'] ?? ''));
    if (!preg_match('/[?&]r=((tg|pg)\d\ds?)(?![a-z0-9])/', $to, $m)) return '';
    $cur = null; foreach (eh_stm_rounds() as $r) if ($r['id'] === $m[1]) $cur = $r;
    if (!$cur) return '';
    $inf = eh_tp_info($cur['series']);
    $h = '<label for="eh-tpsub">' . esc_html($inf['title']) . ' — विषय / Subject</label><select id="eh-tpsub" style="width:100%;padding:11px 12px;border-radius:10px;border:1px solid #cfd8e3;font:inherit;font-size:15px;margin-bottom:6px" onchange="var f=this.form;f.querySelector(\'[name=redirect_to]\').value=this.value">';
    foreach (eh_stm_rounds() as $r) if (($r['series'] ?? '') === $cur['series'] && ($r['kind'] ?? '') === ($cur['kind'] ?? '')) $h .= '<option value="' . esc_attr(eh_tp_url($r)) . '"' . selected($r['id'], $cur['id'], false) . '>' . esc_html($r['sub'] . ' (' . $r['sub_en'] . ')') . '</option>';
    return $h . '</select>';
}
function eh_live_label() { return 'Live Mock'; }

/* the three exam cards — used on /mock-test/ and on the home page */
function eh_mock_cards() {
    $stet = eh_stm_round(); $sl = eh_stm_status($stet) === 'live';
    $T = eh_tp_subjects(); $ready = ['tgt' => 0, 'pgt' => 0];
    foreach (eh_tp_rounds() as $r) if (eh_tp_ready($r)) $ready[$r['series']]++;
    $c = function ($href, $tag, $title, $sub, $rows, $live, $btn, $mod) {
        $tp = strpos($mod, 'mx-c2') !== false ? 'tgt' : (strpos($mod, 'mx-c3') !== false ? 'pgt' : '');
        $h = '<a class="mx-card ' . $mod . '" href="' . esc_url($href) . '"' . ($tp ? ' data-tp="' . $tp . '"' : '') . '><div class="mx-card-top"><span class="mx-tag">' . esc_html($tag) . '</span>'
            . ($live ? '<span class="mx-live"><i></i>LIVE</span>' : '') . '</div><h3>' . $title . '</h3><p class="mx-sub">' . esc_html($sub) . '</p><ul>';
        foreach ($rows as $x) $h .= '<li>' . $x . '</li>';
        return $h . '</ul><span class="mx-go">' . esc_html($btn) . ' <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>';
    };
    return '<div class="mx-cards">'
        . $c(add_query_arg('x', 'stet', home_url('/mock-test/')), 'प्राथमिक · कक्षा 1–5', 'UP Super <em>TET</em>', 'सहायक अध्यापक भर्ती परीक्षा 2026', ['<b>120</b> प्रश्न · <b>360</b> अंक · <b>2</b> घंटे', '10 सेक्शन, नए पैटर्न पर', 'पिछले मॉक के परिणाम व उत्तर-कुंजी'], $sl, $sl ? 'मॉक दें' : 'मॉक देखें', 'mx-c1')
        . $c(add_query_arg('x', 'tgt', home_url('/mock-test/')), 'स्नातक श्रेणी · ' . count($T['tgt']) . ' विषय', 'UP <em>TGT</em>', 'सहायक अध्यापक · विज्ञापन 07/2026', ['<b>30</b> सामान्य अध्ययन + <b>90</b> विषय', 'हर विषय का अलग मॉक', '<b>' . $ready['tgt'] . '</b> विषय लाइव · बाकी Coming Soon'], $ready['tgt'] > 0, 'विषय चुनें', 'mx-c2')
        . $c(add_query_arg('x', 'pgt', home_url('/mock-test/')), 'प्रवक्ता · ' . count($T['pgt']) . ' विषय', 'UP <em>PGT</em>', 'प्रवक्ता (स्नातकोत्तर) · विज्ञापन 06/2026', ['<b>30</b> सामान्य अध्ययन + <b>90</b> विषय', 'हर विषय का अलग मॉक', '<b>' . $ready['pgt'] . '</b> विषय लाइव · बाकी Coming Soon'], $ready['pgt'] > 0, 'विषय चुनें', 'mx-c3')
        . '</div>' . eh_tp_picker();
}

/* home hero: the old Super TET style navy boxes, one each for Super TET, UP TGT, UP PGT */
function eh_home_live_boxes() {
    $logo = esc_url(EH_URI . '/assets/upsssc-alert-logo.svg');
    $stet = eh_stm_round(); $sl = eh_stm_status($stet) === 'live';
    $T = eh_tp_subjects(); $ready = ['tgt' => 0, 'pgt' => 0];
    foreach (eh_tp_rounds() as $r) if (eh_tp_ready($r)) $ready[$r['series']]++;
    $end = (new DateTime(EH_TP_END, new DateTimeZone('Asia/Kolkata')));
    $hm = ['', 'जनवरी', 'फ़रवरी', 'मार्च', 'अप्रैल', 'मई', 'जून', 'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर', 'नवंबर', 'दिसंबर'];
    $endt = $end->format('j') . ' ' . $hm[(int) $end->format('n')];
    $box = function ($href, $live, $chip, $t1, $t2, $meta, $left, $btn) use ($logo) {
        $h = '<a class="uh-stm uh-lb' . ($live ? ' live' : '') . '" href="' . esc_url($href) . '"><span class="uh-stm-glow"></span>'
            . '<span class="uh-stm-top"><img src="' . $logo . '" alt="UPSSSC ALERT" width="62" height="62"><span><span class="uh-stm-chip"><i></i><span>' . esc_html($chip) . '</span></span>'
            . '<b class="uh-stm-t">' . esc_html($t1) . ' <em>' . esc_html($t2) . '</em></b></span></span><span class="uh-stm-meta">';
        foreach ($meta as $m) $h .= '<span>' . esc_html($m) . '</span>';
        return $h . '</span><span class="uh-stm-bot"><span class="uh-lb-l">' . $left . '</span><span class="uh-stm-go">' . esc_html($btn) . '</span></span></a>';
    };
    return '<div class="uh-lbs">'
        . $box(add_query_arg('x', 'tgt', home_url('/mock-test/')), $ready['tgt'] > 0, 'LIVE · फ्री विषयवार मॉक', 'UP TGT 2026', 'लाइव मॉक टेस्ट', ['📚 ' . count($T['tgt']) . ' विषय', '📝 30 GS + 90 विषय', '🎯 360 अंक · +3/−1', '🏆 रैंक + परसेंटाइल'], '<b>' . $ready['tgt'] . '</b> विषय लाइव · ' . esc_html($endt) . ' तक खुला', 'विषय चुनें →')
        . $box(add_query_arg('x', 'pgt', home_url('/mock-test/')), $ready['pgt'] > 0, ($ready['pgt'] ? 'LIVE' : 'Coming Soon') . ' · फ्री विषयवार मॉक', 'UP PGT 2026', 'लाइव मॉक टेस्ट', ['📚 ' . count($T['pgt']) . ' विषय', '📝 30 GS + 90 विषय', '🎯 360 अंक · +3/−1', '🏆 रैंक + परसेंटाइल'], '<b>' . $ready['pgt'] . '</b> विषय लाइव · बाकी Coming Soon', 'विषय चुनें →')
        . $box(home_url('/super-tet-mock/'), $sl, $sl ? 'LIVE · अभी चल रहा है' : 'मॉक 1 व मॉक 2 · परिणाम', 'UP Super TET 2026', 'लाइव मॉक टेस्ट', ['📝 120 प्रश्न · 360 अंक', '⏱ 2 घंटे', '📘 उत्तर-कुंजी + व्याख्या', '🌐 हिंदी + English'], $sl ? 'मॉक अभी खुला है' : 'रैंक, उत्तर-कुंजी और व्याख्या देखें', $sl ? 'अभी मॉक दें →' : 'परिणाम देखें →')
        . '</div>';
}

/* ---------- student's chosen subject (saved on the server, changeable any time) ---------- */
function eh_tp_pref($uid, $series) {
    $v = $uid ? (string) get_user_meta($uid, 'eh_tpsub_' . $series, true) : '';
    return isset(eh_tp_subjects()[$series][$v]) ? $v : '';
}
add_action('rest_api_init', function () {
    register_rest_route('eh/v1', '/tp/pref', ['methods' => 'POST', 'permission_callback' => function () { return is_user_logged_in(); }, 'callback' => function (WP_REST_Request $q) {
        $s = sanitize_key($q['series'] ?? ''); $sub = sanitize_key($q['sub'] ?? '');
        if (!isset(eh_tp_subjects()[$s][$sub])) return new WP_Error('bad', 'bad subject', ['status' => 400]);
        update_user_meta(get_current_user_id(), 'eh_tpsub_' . $s, $sub);
        return ['ok' => true];
    }]);
});
/* this user's attempts per round: last submitted score, best, attempts, and whether a test is running */
function eh_tp_my($uid) {
    if (!$uid) return [];
    global $wpdb; $out = [];
    foreach ($wpdb->get_results($wpdb->prepare('SELECT round, att, started, submitted, score, correct, wrong FROM ' . eh_stm_table() . ' WHERE uid = %d ORDER BY att', $uid), ARRAY_A) as $x) {
        $o = $out[$x['round']] ?? ['n' => 0, 'last' => null, 'best' => null, 'run' => false];
        if ($x['submitted'] > 0) { $o['n']++; $o['last'] = (int) $x['score']; $o['best'] = $o['best'] === null ? (int) $x['score'] : max($o['best'], (int) $x['score']); $o['run'] = false; }
        elseif ($x['started'] > 0) $o['run'] = true;
        $out[$x['round']] = $o;
    }
    return $out;
}
function eh_tp_find($id) { static $ix = null, $k = null; $v = eh_stm_rounds_ver(); if ($ix === null || $k !== $v) { $ix = array_column(eh_stm_rounds(), null, 'id'); $k = $v; } return $ix[$id] ?? null; }
/* one test card: title, pattern, status, start / continue / retest, and a thin score bar once attempted */
function eh_tp_test_card($r, $my, $title, $sub, $tag = '') {
    $ok = eh_stm_status($r) === 'live'; $m = $my[$r['id']] ?? null; $max = eh_stm_nq($r) * 3; $u = eh_tp_url($r);
    $h = '<div class="mt-c' . ($ok ? '' : ' soon') . '"><div class="mt-top"><span class="mt-tag">' . esc_html($tag) . '</span>' . ($ok ? '<span class="mt-live"><i></i>LIVE</span>' : '<span class="mt-soon">Coming Soon</span>') . '</div>'
        . '<h3>' . esc_html($title) . '</h3><p>' . esc_html($sub) . '</p>'
        . '<ul class="mt-meta"><li><b>' . eh_stm_nq($r) . '</b> प्रश्न</li><li><b>' . $max . '</b> अंक</li><li><b>' . (int) round(eh_stm_secs($r) / 60) . '</b> मिनट</li><li><b>+3 / −1</b></li></ul>';
    if (!$ok) return $h . '<span class="mt-btn dis">Coming Soon</span></div>';
    if ($m && $m['run']) $h .= '<a class="mt-btn" href="' . esc_url($u) . '">▶ परीक्षा जारी रखें</a>';
    elseif ($m && $m['n']) $h .= '<div class="mt-two"><a class="mt-btn ghost" href="' . esc_url($u) . '">View Results</a><a class="mt-btn" href="' . esc_url(add_query_arg('re', 1, $u)) . '">↻ Reattempt</a></div>';
    else $h .= '<a class="mt-btn" href="' . esc_url($u) . '">मॉक शुरू करें →</a>';
    if ($m && $m['n']) { $pc = max(0, min(100, round($m['last'] * 100 / $max))); $h .= '<div class="mt-done"><div class="mt-bar"><i style="width:' . $pc . '%"></i></div><span>✓ Attempted · <b>' . $m['last'] . '</b> / ' . $max . ($m['n'] > 1 ? ' · ' . $m['n'] . ' Attempts · Best ' . $m['best'] : '') . '</span></div>'; }
    return $h . '</div>';
}

/* Subject picker popup: opens when a student clicks UP TGT / UP PGT for the first time (or on "बदलें").
   The choice is saved to the account (server) and in the browser, and the student lands on that subject's test zone. */
function eh_tp_picker() {
    static $done = false; if ($done) return ''; $done = true;
    $uid = get_current_user_id(); $hub = home_url('/mock-test/'); $data = [];
    foreach (eh_tp_subjects() as $s => $list) {
        $i = 0; $data[$s] = ['t' => eh_tp_info($s)['title'], 'p' => eh_tp_pref($uid, $s), 'l' => []];
        foreach ($list as $slug => $nm) { $i++; $r = ['paper' => 'tp/' . $s . '-' . $slug, 'gs' => 'tp/gs']; $data[$s]['l'][] = [$slug, $nm[0], $nm[1], eh_tp_ready($r) ? 1 : 0]; }
    }
    $cfg = ['hub' => $hub, 'in' => (bool) $uid, 'rest' => esc_url_raw(rest_url('eh/v1/tp/pref')), 'nonce' => $uid ? wp_create_nonce('wp_rest') : '', 'd' => $data];
    ob_start(); ?>
<div class="tpk" id="tpk" hidden><div class="tpk-bg" data-x></div><div class="tpk-box" role="dialog" aria-modal="true" aria-labelledby="tpk-t">
 <div class="tpk-hd"><div><span class="tpk-k" id="tpk-k">UP TGT 2026</span><h3 id="tpk-t">आप किस विषय की तैयारी कर रहे हैं?</h3><p>एक बार चुनें — आपका विषय आपके अकाउंट में सेव रहेगा और अगली बार सीधे आपके विषय के टेस्ट खुलेंगे। कभी भी बदल सकते हैं।</p></div><button class="tpk-x" data-x aria-label="बंद करें">×</button></div>
 <label class="tpk-s"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input type="search" id="tpk-q" placeholder="विषय खोजें — जैसे गणित, English"></label>
 <div class="tpk-g" id="tpk-g"></div>
 <div class="tpk-ft" id="tpk-ft"></div>
</div></div>
<script>(function(){var C=<?php echo wp_json_encode($cfg); ?>,M=document.getElementById('tpk'),G=document.getElementById('tpk-g'),Q=document.getElementById('tpk-q'),S='';
function zone(s,sub){return C.hub+'?x='+s+'&s='+sub}
function local(s){try{return localStorage.getItem('eh_tps_'+s)||''}catch(e){return ''}}
function draw(){var d=C.d[S],v=(Q.value||'').trim().toLowerCase(),cur=d.p||local(S);document.getElementById('tpk-k').textContent=d.t;
 G.innerHTML=d.l.filter(function(x){return !v||(x[1]+' '+x[2]).toLowerCase().indexOf(v)>-1}).map(function(x,i){return '<button type="button" class="tpk-o'+(x[0]===cur?' on':'')+(x[3]?'':' soon')+'" data-s="'+x[0]+'"><b>'+x[1]+'</b><small>'+x[2]+'</small><em>'+(x[0]===cur?'✓ आपका विषय':(x[3]?'● LIVE':'Coming Soon'))+'</em></button>'}).join('')||'<p class="tpk-e">कोई विषय नहीं मिला।</p>';
 document.getElementById('tpk-ft').innerHTML=C.in?'':'💡 <a href="'+'<?php echo esc_js(eh_login_page_url($hub)); ?>'+'">लॉग-इन</a> करने पर आपका विषय अकाउंट में सेव रहेगा।'}
function open(s){S=s;Q.value='';draw();M.hidden=false;document.documentElement.style.overflow='hidden';setTimeout(function(){try{Q.focus({preventScroll:true})}catch(e){}},50)}
function close(){M.hidden=true;document.documentElement.style.overflow=''}
function pick(sub){try{localStorage.setItem('eh_tps_'+S,sub)}catch(e){}var go=function(){location.href=zone(S,sub)};
 if(C.in){fetch(C.rest,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':C.nonce},body:JSON.stringify({series:S,sub:sub})}).then(go,go)}else go()}
G.addEventListener('click',function(e){var b=e.target.closest('[data-s]');if(b)pick(b.dataset.s)});Q.addEventListener('input',draw);
[].forEach.call(M.querySelectorAll('[data-x]'),function(x){x.onclick=close});document.addEventListener('keydown',function(e){if(e.key==='Escape'&&!M.hidden)close()});
document.addEventListener('click',function(e){var k=e.target.closest('[data-tp-pick]');if(k){e.preventDefault();S=k.dataset.tpPick;pick(k.dataset.sub);return}var a=e.target.closest('[data-tp]');if(!a)return;var s=a.dataset.tp,p=C.d[s].p||local(s);
 if(p&&!a.hasAttribute('data-tp-change')){a.href=zone(s,p);return}e.preventDefault();open(s)},true);
window.ehTpOpen=open;var m=location.search.match(/[?&]pick=(tgt|pgt)/);if(m)open(m[1])})();</script>
<?php return ob_get_clean();
}

/* test list row (Mock No. 1, 2 …): Start goes straight into the test (or to login first); attempted rows show score, rank, analysis, re-attempt */
function eh_mock_url($r) {
    if (eh_tp_is($r)) return eh_tp_url($r);
    return $r['id'] === eh_stm_round()['id'] ? home_url('/super-tet-mock/') : add_query_arg('r', $r['id'], home_url('/super-tet-mock/'));
}
function eh_tp_test_row($r, $my, $no, $title, $lang = 'हिंदी + English') {
    $st = eh_stm_status($r); $ok = $st === 'live'; $over = $st === 'over'; $m = $my[$r['id']] ?? null; $max = eh_stm_nq($r) * 3; $u = eh_mock_url($r); $in = is_user_logged_in();
    $hm = ['', 'जनवरी', 'फ़रवरी', 'मार्च', 'अप्रैल', 'मई', 'जून', 'जुलाई', 'अगस्त', 'सितंबर', 'अक्टूबर', 'नवंबर', 'दिसंबर'];
    $dt = eh_tp_is($r) ? '' : '<span>📅 ' . wp_date('j', $r['start_ts']) . ' ' . $hm[(int) wp_date('n', $r['start_ts'])] . ' ' . wp_date('Y', $r['start_ts']) . '</span>';
    $tt = $title; $tp = ''; if (preg_match('/^(.*\bTOPIC TEST \d+) \((.+)\)$/u', $title, $mm)) { $tt = $mm[1]; $tp = $mm[2]; }
    $go = function ($url) use ($in) { $url = add_query_arg('go', 1, $url); return $in ? $url : eh_login_page_url($url); };
    $h = '<div class="tr' . ($ok || $over ? '' : ' soon') . ($m && $m['n'] ? ' done' : '') . '"><div class="tr-n">' . str_pad((string) $no, 2, '0', STR_PAD_LEFT) . '</div><div class="tr-b">'
        . '<div class="tr-t"><b>' . esc_html($tt) . '</b>' . ($ok ? '<span class="tr-free">FREE</span><span class="tr-live"><i></i>LIVE</span>' : ($over ? '<span class="tr-soon">समाप्त</span>' : '<span class="tr-soon">' . (eh_tp_is($r) ? 'Coming Soon' : 'आगामी') . '</span>')) . '</div>'
        . ($tp !== '' ? '<div class="tr-tp">' . esc_html($tp) . '</div>' : '') . '<div class="tr-m">' . $dt . '<span>📝 ' . eh_stm_nq($r) . ' प्रश्न</span><span>🎯 ' . $max . ' अंक</span><span>⏱ ' . (int) round(eh_stm_secs($r) / 60) . ' मिनट</span><span>🌐 भाषा: ' . esc_html($lang) . '</span></div>';
    if ($m && $m['n']) {
        global $wpdb; $rank = 1 + (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . eh_stm_table() . ' WHERE round = %s AND submitted > 0 AND counted = 1 AND score > %d', $r['id'], (int) $m['last']));
        $pc = max(0, min(100, round($m['last'] * 100 / $max)));
        $h .= '<div class="tr-s"><div class="tr-bar"><i style="width:' . $pc . '%"></i></div><span>✓ Attempted · <b>' . $m['last'] . '</b>/' . $max . ' · Rank ~' . $rank . ($m['n'] > 1 ? ' · ' . $m['n'] . ' Attempts' : '') . '</span></div>';
    }
    $h .= '</div><div class="tr-a">';
    if ($over && $m && $m['n']) $h .= '<a class="tr-btn" href="' . esc_url($go($u)) . '">📊 View Results</a>';
    elseif ($over) $h .= '<a class="tr-btn ghost" href="' . esc_url($u) . '">उत्तर-कुंजी देखें</a>';
    elseif (!$ok) $h .= '<span class="tr-btn dis">' . (eh_tp_is($r) ? 'Coming Soon' : 'आगामी') . '</span>';
    elseif ($m && $m['run']) $h .= '<a class="tr-btn" href="' . esc_url($go($u)) . '">▶ जारी रखें</a>';
    elseif ($m && $m['n']) $h .= '<a class="tr-btn ghost" href="' . esc_url($go($u)) . '">📊 View Results</a><a class="tr-btn" href="' . esc_url(add_query_arg('re', 1, $u)) . '">↻ Reattempt</a>';
    else $h .= '<a class="tr-btn" href="' . esc_url($go($u)) . '">Start Test →</a>';
    return $h . '</div></div>';
}
