<?php
if (!defined('ABSPATH')) exit;

/* Exam guide posts — built from exam data, created/updated from admin (category: typing-tips) */
function eh_blog_orgs() {
    return [
        'ssc' => 'SSC (CHSL, CGL, DEST)', 'dsssb' => 'DSSSB', 'upsssc' => 'UPSSSC', 'marathon' => 'Marathon Typing', 'dhc' => 'Delhi High Court',
        'uppsc' => 'UPPSC Computer Operator/Assistant', 'uppsc-ro-aro' => 'UPPSC RO/ARO', 'jnu' => 'JNU Assistant', 'phhc' => 'Punjab & Haryana High Court',
        'ssb' => 'SSB HCM', 'cghc' => 'Chhattisgarh High Court', 'bcc' => 'Bihar Civil Court & Patna High Court', 'mpcpct' => 'MP CPCT', 'kvs' => 'KVS',
        'mp-police' => 'MP Police ASI', 'up-police' => 'UP Police SI/ASI', 'rssb' => 'Rajasthan RSSB', 'ahc' => 'Allahabad High Court',
        'delhi-hcm' => 'Delhi Police HCM', 'jkssb' => 'JKSSB Junior Assistant', 'jeevika' => 'Bihar Jeevika', 'aphc' => 'Andhra Pradesh High Court',
        'bhu' => 'BHU Junior Clerk', 'csir' => 'CSIR JSA', 'uksssc' => 'Uttarakhand UKSSSC & UKPSC RO/ARO', 'sci' => 'Supreme Court (SCI)',
        'bhc' => 'Bombay High Court', 'hcraj' => 'Rajasthan High Court', 'aiims' => 'AIIMS CRE', 'tshc' => 'Telangana High Court',
        'cbse' => 'CBSE & Navodaya (NVS JSA)', 'bpssc' => 'Bihar Police BPSSC', 'mpsc' => 'MPSC Group C', 'ossc' => 'Odisha OSSC',
        'mpcourt' => 'MP High Court (Jabalpur)', 'ntpc' => 'RRB NTPC', 'jci' => 'Jute Corporation (JCI)', 'dda' => 'DDA JSA/ASO',
        'airforce' => 'Air Force Group C', 'jssc' => 'JSSC Inter Level', 'hcm' => 'BSF & All Forces HCM', 'spmcil' => 'SPMCIL',
        'jhc' => 'Jharkhand High Court', 'wbpsc' => 'WBSSC Clerkship', 'curec' => 'NTA (Central Universities, IGNOU)', 'emrs' => 'EMRS JSA',
        'djb' => 'Delhi Jal Board', 'agniveer' => 'Army Agniveer Clerk', 'ukhcrec' => 'Uttarakhand High Court', 'beltron' => 'Bihar Beltron DEO',
        'bvss' => 'Bihar Vidhan Sabha & Vidhan Parishad', 'ukpsc' => 'UKPSC APS', 'bssc' => 'BSSC Inter Level',
    ];
}

function eh_blog_groups() {
    $g = [];
    foreach (eh_exams() as $e) {
        $f = strpos($e['slug'], 'marathon') === 0 ? 'marathon' : eh_window($e['slug'])['family'];
        $g[$f][] = $e;
    }
    return $g;
}

function eh_blog_screen_text($w) {
    $t = [];
    switch ($w['layout']) {
        case 'sidea': case 'sidec': $t[] = 'स्क्रीन पर पैसेज बाईं तरफ़ और टाइपिंग बॉक्स दाईं तरफ़ साथ-साथ होता है, इसलिए नज़र बार-बार ऊपर-नीचे नहीं करनी पड़ती।'; break;
        case 'nopass': $t[] = 'इसमें स्क्रीन पर पैसेज नहीं दिखता — पैसेज कागज़ पर छपा मिलता है और उसे देखकर पूरी स्क्रीन वाले बड़े बॉक्स में टाइप करना होता है (Paper to Screen)। इसलिए बिना स्क्रीन देखे टाइप करने की आदत डालें।'; break;
        case 'nokbc': case 'nokbb': $t[] = 'ऊपर पैसेज और नीचे टाइपिंग बॉक्स होता है, बीच में कीबोर्ड/भाषा वाली नीली पट्टी नहीं होती।'; break;
        case 'ntpc': $t[] = 'पैसेज ऊपर चौड़े बॉक्स में और टाइपिंग बॉक्स उसके नीचे थोड़ा दाईं ओर छोटा होता है।'; break;
        case 'c': $t[] = 'पैसेज और टाइपिंग बॉक्स पूरी स्क्रीन की चौड़ाई में ऊपर-नीचे होते हैं।'; break;
        default: $t[] = 'ऊपर नीली पट्टी में Keyboard Layout और भाषा लिखी होती है, उसके नीचे पैसेज और फिर टाइपिंग बॉक्स।';
    }
    if (!empty($w['stats'])) $t[] = 'दाईं ओर Typing Stats में Keystrokes, Backspace, Word Error जैसी गिनती चलती रहती है।';
    if (!empty($w['tool'])) $t[] = 'ऊपर बाएं टूलबार से आवाज़ बंद-चालू, Full screen और font छोटा-बड़ा कर सकते हैं।';
    if (strpos($w['ff'], 'Times') === 0 || strpos($w['ff'], '"Times') === 0) $t[] = 'पैसेज Times जैसे serif font में दिखता है।';
    if (!empty($w['tbg'])) $t[] = 'टाइपिंग बॉक्स हल्के हरे रंग का होता है।';
    return implode(' ', $t);
}

function eh_blog_post_html($key, $org, $list) {
    $w = eh_window($list[0]['slug']);
    $langs = array_unique(array_map(function ($e) { return eh_lang_label($e['lang']); }, $list));
    $keys = array_filter($list, function ($e) { return $e['unit'] === 'keys'; });
    $h = '<p><b>' . esc_html($org) . '</b> की भर्ती में टाइपिंग टेस्ट qualifying होता है — यानी तय समय में तय स्पीड और कम गलती के साथ टाइप करना ज़रूरी है। इस पोस्ट में ' . esc_html($org) . ' के ' . count($list) . ' टाइपिंग टेस्ट का टारगेट, समय, गलती गिनने का तरीका और प्रैक्टिस का आसान प्लान दिया गया है। सारे टेस्ट हमारी वेबसाइट पर बिल्कुल फ्री हैं।</p>';
    $h .= '<h2>' . esc_html($org) . ' Typing Test — टारगेट और समय</h2><table><thead><tr><th>Test</th><th>भाषा</th><th>टारगेट</th><th>समय</th><th>प्रैक्टिस</th></tr></thead><tbody>';
    foreach ($list as $e) $h .= '<tr><td>' . esc_html($e['name']) . '</td><td>' . esc_html(eh_lang_label($e['lang'])) . '</td><td>' . esc_html(eh_target_label($e)) . '</td><td>' . (int) $e['minutes'] . ' मिनट</td><td><a href="' . esc_url(home_url('/typing/' . $e['slug'] . '/')) . '">टेस्ट दें →</a></td></tr>';
    $h .= '</tbody></table>';
    if ($keys) $h .= '<p><b>Key depressions</b> का मतलब है टाइप करते समय दबाई गई keys की गिनती। जैसे ' . esc_html(reset($keys)['name']) . ' में ' . (int) reset($keys)['minutes'] . ' मिनट में ' . number_format_i18n(reset($keys)['target']) . ' key depressions चाहिए — मोटे तौर पर हर शब्द में 5 keys मानें तो करीब ' . (int) round(reset($keys)['target'] / 5 / max(1, reset($keys)['minutes'])) . ' शब्द प्रति मिनट की स्पीड बनती है।</p>';
    $h .= '<h2>टेस्ट की स्क्रीन कैसी होती है</h2><p>' . esc_html(eh_blog_screen_text($w)) . ' हमारी वेबसाइट पर ' . esc_html($org) . ' का टेस्ट इसी तरह की स्क्रीन पर खुलता है, ताकि एग्ज़ाम वाले दिन सब कुछ जाना-पहचाना लगे।</p>';
    $h .= '<h2>गलतियां कैसे गिनी जाती हैं</h2><ul><li><b>Full mistake:</b> गलत शब्द, छूटा हुआ शब्द, या अपनी तरफ़ से जोड़ा गया फालतू शब्द।</li><li><b>Half mistake:</b> स्पेस की गलती (दो शब्द जुड़ गए या एक शब्द टूट गया), कैपिटल-छोटे अक्षर की गलती, कॉमा-फुलस्टॉप जैसी विराम चिह्न की गलती।</li><li><b>Error %</b> = (Full + Half ÷ 2) ÷ टाइप किए गए शब्द × 100</li><li><b>Net Speed</b> = (टाइप किए शब्द − कुल गलती) ÷ मिनट</li></ul>';
    $h .= '<p>आधिकारिक नियम हर भर्ती के notification में अलग से दिए होते हैं — एग्ज़ाम से पहले अपना notification ज़रूर पढ़ें। ऊपर दिया तरीका हमारी प्रैक्टिस में इस्तेमाल होता है और ज़्यादातर typing test इसी तरह जांचे जाते हैं।</p>';
    $h .= '<h2>7 दिन का प्रैक्टिस प्लान</h2><ol><li><b>दिन 1-2:</b> स्पीड की चिंता छोड़कर सिर्फ़ सही टाइप करें। Accuracy 95% से ऊपर रखें।</li><li><b>दिन 3-4:</b> रोज़ 3 टेस्ट दें — सुबह, दोपहर और शाम (Exam Hall Typing में तय समय पर नया पैसेज आता है)।</li><li><b>दिन 5:</b> Typing Insight में देखें कौन से शब्द बार-बार गलत हो रहे हैं, उन्हें अलग से 10-10 बार टाइप करें।</li><li><b>दिन 6:</b> Special Passages (कोर्ट आदेश, ऑफ़िस ऑर्डर) पर प्रैक्टिस करें, इनमें मुश्किल शब्द ज़्यादा होते हैं।</li><li><b>दिन 7:</b> Live Typing में हिस्सा लें और देखें दूसरे छात्रों के मुकाबले आपकी रैंक क्या है।</li></ol>';
    if (in_array('Hindi', $langs, true)) $h .= '<h2>हिंदी टाइपिंग वालों के लिए</h2><p>पहले पक्का कर लें कि एग्ज़ाम में <b>Krutidev (Remington)</b>, <b>Remington Gail</b> या <b>Inscript (Mangal)</b> में से कौन सा layout मिलेगा। जिस layout में एग्ज़ाम है, उसी में प्रैक्टिस करें — बीच में layout बदलने से उंगलियों की आदत बिगड़ती है। हमारी वेबसाइट पर टेस्ट शुरू करने से पहले चारों में से कोई भी layout चुन सकते हैं।</p>';
    $h .= '<h2>अक्सर पूछे जाने वाले सवाल</h2>';
    $q = [
        ['क्या ' . $org . ' की typing प्रैक्टिस यहां फ्री है?', 'हां, सारे टेस्ट, रिज़ल्ट और PDF पैसेज पूरी तरह फ्री हैं।'],
        ['Backspace चलेगा या नहीं?', 'हमारे टेस्ट में Backspace चालू रहता है, लेकिन Settings से बंद या सीमित कर सकते हैं। असली एग्ज़ाम का नियम notification में देखें।'],
        ['रिज़ल्ट कहां दिखेगा?', 'टेस्ट खत्म होते ही Performance Dashboard खुलता है जिसमें speed, गलतियां और शब्द-दर-शब्द तुलना होती है। Login करने पर सारे रिज़ल्ट Typing History में सेव रहते हैं।'],
    ];
    foreach ($q as $x) $h .= '<h3>' . esc_html($x[0]) . '</h3><p>' . esc_html($x[1]) . '</p>';
    $h .= '<p><a href="' . esc_url(home_url('/typing/' . $list[0]['slug'] . '/')) . '"><b>' . esc_html($list[0]['name']) . ' की प्रैक्टिस अभी शुरू करें →</b></a></p>';
    return $h;
}

function eh_blog_seed() {
    $cat = get_category_by_slug('typing-tips');
    if ($cat) $cid = $cat->term_id;
    else { $r = wp_insert_term('Typing Tips', 'category', ['slug' => 'typing-tips']); $cid = is_wp_error($r) ? 0 : (int) $r['term_id']; }
    $orgs = eh_blog_orgs(); $made = 0; $upd = 0;
    foreach (eh_blog_groups() as $key => $list) {
        $org = $orgs[$key] ?? strtoupper($key);
        $slug = sanitize_title($key . '-typing-test-rules');
        $data = [
            'post_title' => $org . ' Typing Test ' . wp_date('Y') . ': टारगेट, समय, गलती का नियम और फ्री प्रैक्टिस',
            'post_name' => $slug, 'post_content' => eh_blog_post_html($key, $org, $list),
            'post_status' => 'publish', 'post_type' => 'post', 'post_category' => [$cid],
            'post_excerpt' => $org . ' typing test का टारगेट, समय, error calculation और 7 दिन का प्रैक्टिस प्लान — हिंदी में, फ्री प्रैक्टिस लिंक के साथ।',
        ];
        $ex = get_posts(['post_type' => 'post', 'meta_key' => 'eh_seed', 'meta_value' => $key, 'post_status' => 'any', 'numberposts' => 1]);
        if ($ex) {
            if (get_post_meta($ex[0]->ID, 'eh_seed_locked', true)) continue;
            $data['ID'] = $ex[0]->ID; unset($data['post_name']); wp_update_post($data); $upd++;
        } else { $id = wp_insert_post($data); if ($id && !is_wp_error($id)) { update_post_meta($id, 'eh_seed', $key); $made++; } }
    }
    return "नई पोस्ट: $made · अपडेट: $upd";
}

add_action('admin_post_eh_blogseed', function () {
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    check_admin_referer('eh_blogseed');
    wp_safe_redirect(admin_url('admin.php?page=eh-settings&ehb=' . rawurlencode(eh_blog_seed())));
    exit;
});

add_action('eh_settings_after', function () {
    echo '<hr><h2>Exam guide blog posts</h2>';
    if (!empty($_GET['ehb'])) echo '<div class="notice notice-success"><p id="eh-blog-msg">' . esc_html(sanitize_text_field(wp_unslash($_GET['ehb']))) . '</p></div>';
    echo '<p>हर एग्ज़ाम ग्रुप की गाइड पोस्ट (Typing Tips कैटेगरी) बनाएं/अपडेट करें। जिस पोस्ट को आपने खुद एडिट किया हो उस पर custom field <code>eh_seed_locked</code> = 1 लगा दें, वो दोबारा नहीं बदलेगी।</p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="eh_blogseed">';
    wp_nonce_field('eh_blogseed');
    submit_button('Blog posts बनाएं / अपडेट करें', 'secondary', 'submit', false, ['id' => 'eh-blog-btn']);
    echo '</form>';
}, 20);
