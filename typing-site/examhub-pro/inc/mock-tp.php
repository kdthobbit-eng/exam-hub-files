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
                'name' => $inf['short'] . ' ' . $nm[0] . ' — मॉक 1', 'login' => EH_TP_LOGIN, 'start' => EH_TP_LOGIN, 'end' => EH_TP_END,
                'paper' => 'tp/' . $s . '-' . $slug, 'gs' => 'tp/gs'];
        }
    }
    return $out;
}
function eh_tp_is($r) { return in_array($r['series'] ?? '', ['tgt', 'pgt'], true); }
/* a subject paper is ready only when both its GS file and its subject file exist */
function eh_tp_ready($r) {
    return is_file(EH_DIR . '/data/' . $r['paper'] . '.json') && is_file(EH_DIR . '/data/' . $r['gs'] . '.json');
}
/* ?r=tg01 / pg05 → that subject round */
function eh_tp_req_round() {
    $id = sanitize_key($_GET['r'] ?? ($_REQUEST['eh_r'] ?? ''));
    if (!preg_match('/^(tg|pg)\d\d$/', $id)) return null;
    foreach (eh_stm_rounds() as $r) if ($r['id'] === $id) return $r;
    return null;
}
function eh_tp_url($r) { return add_query_arg('r', $r['id'], home_url('/super-tet-mock/')); }
/* on the login / signup form: subject dropdown, pre-selected from the mock the candidate came from; changing it changes where they land */
function eh_tp_auth_select() {
    $to = rawurldecode((string) ($_REQUEST['redirect_to'] ?? ''));
    if (!preg_match('/[?&]r=((tg|pg)\d\d)/', $to, $m)) return '';
    $cur = null; foreach (eh_stm_rounds() as $r) if ($r['id'] === $m[1]) $cur = $r;
    if (!$cur) return '';
    $inf = eh_tp_info($cur['series']);
    $h = '<label for="eh-tpsub">' . esc_html($inf['title']) . ' — विषय / Subject</label><select id="eh-tpsub" style="width:100%;padding:11px 12px;border-radius:10px;border:1px solid #cfd8e3;font:inherit;font-size:15px;margin-bottom:6px" onchange="var f=this.form;f.querySelector(\'[name=redirect_to]\').value=this.value">';
    foreach (eh_stm_rounds() as $r) if (($r['series'] ?? '') === $cur['series']) $h .= '<option value="' . esc_attr(eh_tp_url($r)) . '"' . selected($r['id'], $cur['id'], false) . '>' . esc_html($r['sub'] . ' (' . $r['sub_en'] . ')') . '</option>';
    return $h . '</select>';
}
function eh_live_label() { return 'Live Mock'; }

/* the three exam cards — used on /mock-test/ and on the home page */
function eh_mock_cards() {
    $stet = eh_stm_round(); $sl = eh_stm_status($stet) === 'live';
    $T = eh_tp_subjects(); $ready = ['tgt' => 0, 'pgt' => 0];
    foreach (eh_tp_rounds() as $r) if (eh_tp_ready($r)) $ready[$r['series']]++;
    $c = function ($href, $tag, $title, $sub, $rows, $live, $btn, $mod) {
        $h = '<a class="mx-card ' . $mod . '" href="' . esc_url($href) . '"><div class="mx-card-top"><span class="mx-tag">' . esc_html($tag) . '</span>'
            . ($live ? '<span class="mx-live"><i></i>LIVE</span>' : '') . '</div><h3>' . $title . '</h3><p class="mx-sub">' . esc_html($sub) . '</p><ul>';
        foreach ($rows as $x) $h .= '<li>' . $x . '</li>';
        return $h . '</ul><span class="mx-go">' . esc_html($btn) . ' <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></a>';
    };
    return '<div class="mx-cards">'
        . $c(home_url('/super-tet-mock/'), 'प्राथमिक · कक्षा 1–5', 'UP Super <em>TET</em>', 'सहायक अध्यापक भर्ती परीक्षा 2026', ['<b>120</b> प्रश्न · <b>360</b> अंक · <b>2</b> घंटे', '10 सेक्शन, नए पैटर्न पर', 'पिछले मॉक के परिणाम व उत्तर-कुंजी'], $sl, $sl ? 'मॉक दें' : 'मॉक देखें', 'mx-c1')
        . $c(add_query_arg('x', 'tgt', home_url('/mock-test/')), 'स्नातक श्रेणी · ' . count($T['tgt']) . ' विषय', 'UP <em>TGT</em>', 'सहायक अध्यापक · विज्ञापन 07/2026', ['<b>30</b> सामान्य अध्ययन + <b>90</b> विषय', 'हर विषय का अलग मॉक', '<b>' . $ready['tgt'] . '</b> विषय लाइव · बाकी जल्द'], $ready['tgt'] > 0, 'विषय चुनें', 'mx-c2')
        . $c(add_query_arg('x', 'pgt', home_url('/mock-test/')), 'प्रवक्ता · ' . count($T['pgt']) . ' विषय', 'UP <em>PGT</em>', 'प्रवक्ता (स्नातकोत्तर) · विज्ञापन 06/2026', ['<b>30</b> सामान्य अध्ययन + <b>90</b> विषय', 'हर विषय का अलग मॉक', '<b>' . $ready['pgt'] . '</b> विषय लाइव · बाकी जल्द'], $ready['pgt'] > 0, 'विषय चुनें', 'mx-c3')
        . '</div>';
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
        . $box(add_query_arg('x', 'pgt', home_url('/mock-test/')), $ready['pgt'] > 0, ($ready['pgt'] ? 'LIVE' : 'जल्द') . ' · फ्री विषयवार मॉक', 'UP PGT 2026', 'लाइव मॉक टेस्ट', ['📚 ' . count($T['pgt']) . ' विषय', '📝 30 GS + 90 विषय', '🎯 360 अंक · +3/−1', '🏆 रैंक + परसेंटाइल'], '<b>' . $ready['pgt'] . '</b> विषय लाइव · बाकी जल्द', 'विषय चुनें →')
        . $box(home_url('/super-tet-mock/'), $sl, $sl ? 'LIVE · अभी चल रहा है' : 'मॉक 1 व मॉक 2 · परिणाम', 'UP Super TET 2026', 'लाइव मॉक टेस्ट', ['📝 120 प्रश्न · 360 अंक', '⏱ 2 घंटे', '📘 उत्तर-कुंजी + व्याख्या', '🌐 हिंदी + English'], $sl ? 'मॉक अभी खुला है' : 'रैंक, उत्तर-कुंजी और व्याख्या देखें', $sl ? 'अभी मॉक दें →' : 'परिणाम देखें →')
        . '</div>';
}
