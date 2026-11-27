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
