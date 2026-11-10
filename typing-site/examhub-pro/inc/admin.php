<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page('UPSSSC Alert', 'UPSSSC Alert', 'manage_options', 'eh-settings', 'eh_settings_page', 'dashicons-keyboard', 3);
});

add_action('admin_init', function () {
    foreach (['brand', 'brand_sub', 'tg_join', 'youtube', 'yt_uploads', 'whatsapp', 'tagline', 'notice', 'tg_group', 'tg_channel', 'allowed_error', 'live_lang', 'exams_hidden', 'exams_extra', 'google_client_id', 'live_schedule', 'upcoming', 'window_map'] as $k) {
        register_setting('eh_settings', 'eh_' . $k, ['sanitize_callback' => function ($v) use ($k) {
            if (in_array($k, ['live_schedule', 'upcoming', 'window_map'], true)) { $j = json_decode(wp_unslash($v), true); return is_array($j) ? wp_json_encode($j, JSON_UNESCAPED_UNICODE) : ''; }
            if ($k === 'exams_extra') { $j = json_decode(wp_unslash($v), true); return is_array($j) ? wp_json_encode($j, JSON_UNESCAPED_UNICODE) : '[]'; }
            if (in_array($k, ['tg_group', 'tg_channel', 'tg_join', 'youtube'], true)) return esc_url_raw($v);
            return sanitize_text_field($v);
        }]);
    }
});

function eh_settings_page() {
    $f = function ($k, $label, $help = '') {
        echo '<tr><th><label for="eh_' . $k . '">' . esc_html($label) . '</label></th><td><input class="regular-text" id="eh_' . $k . '" name="eh_' . $k . '" value="' . esc_attr(get_option('eh_' . $k, '')) . '" placeholder="' . esc_attr(eh_opt($k)) . '">' . ($help ? '<p class="description">' . esc_html($help) . '</p>' : '') . '</td></tr>';
    };
    global $wpdb;
    $total = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . eh_table());
    ?>
    <div class="wrap"><h1>UPSSSC Alert — Settings</h1>
    <p>कुल टेस्ट दिए गए: <b><?php echo number_format_i18n($total); ?></b> · Exams: <b><?php echo count(eh_exams()); ?></b> · Passages: <b><?php echo count(eh_passages()); ?></b> (नया पैसेज: <a href="<?php echo admin_url('post-new.php?post_type=eh_passage'); ?>">Typing Passages → Add</a>)</p>
    <form method="post" action="options.php"><?php settings_fields('eh_settings'); ?>
    <table class="form-table">
    <?php
    $f('brand', 'Site name');
    $f('tagline', 'Tagline');
    $f('notice', 'Top notice bar', 'खाली छोड़ें तो default दिखेगा');
    $f('tg_channel', 'Telegram Channel link');
    $f('tg_group', 'Telegram Group link');
    $f('brand_sub', 'Small line beside the site name', 'Default: by XL Study');
    $f('tg_join', 'Telegram link to join a batch');
    $f('youtube', 'YouTube channel link');
    $f('yt_uploads', 'YouTube playlist IDs for the corner player', 'comma से अलग; खाली = default');
    $f('whatsapp', 'WhatsApp number', 'Country code ke saath, jaise 919455015600');
    $f('allowed_error', 'Allowed error %', 'Default 5');
    $f('live_lang', 'Live typing language', 'en / hi — खाली = दिन के हिसाब से बदलता रहेगा');
    $f('exams_hidden', 'Hidden exam slugs', 'comma से अलग');
    $f('google_client_id', 'Google Client ID', 'Google login के लिए — xxxx.apps.googleusercontent.com');
    ?>
    <tr><th>Extra / edited exams (JSON)</th><td><textarea name="eh_exams_extra" rows="6" class="large-text code"><?php echo esc_textarea(get_option('eh_exams_extra', '[]')); ?></textarea>
    <p class="description">Example: [{"slug":"up-court-clerk-hindi","name":"UP Civil Court Clerk Hindi Typing","lang":"hi","unit":"words","target":300,"minutes":10,"cat":"court"}]</p></td></tr>
    <tr><th>Live Typing schedule (JSON)</th><td><textarea name="eh_live_schedule" rows="5" class="large-text code" placeholder='[{"exam":"ssc-chsl-jsa-ldc-english-typing-test","start":"07:00","end":"10:40"}]'><?php echo esc_textarea(get_option('eh_live_schedule', '')); ?></textarea><p class="description">खाली = default schedule</p></td></tr>
    <tr><th>Upcoming Exams popup (JSON)</th><td><textarea name="eh_upcoming" rows="5" class="large-text code" placeholder='{"confirmed":[{"tag":"UPSSSC","name":"Junior Assistant","sub":"Hindi Mangal","date":"2026-11-13","slug":"upsssc-assistants-hindi-typing-test"}],"next":[]}'><?php echo esc_textarea(get_option('eh_upcoming', '')); ?></textarea></td></tr>
    <tr><th>Exam window override (JSON)</th><td><textarea name="eh_window_map" rows="3" class="large-text code" placeholder='{"exam-slug":"family"}'><?php echo esc_textarea(get_option('eh_window_map', '')); ?></textarea></td></tr>
    </table><?php submit_button(); ?></form>
    <?php do_action('eh_settings_after'); ?></div>
    <?php
}

/* ---------- One-click theme update from GitHub (admin only) ---------- */
function eh_rrmdir($d) {
    if (!is_dir($d)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($d);
}

function eh_self_update($sha = '') {
    $repo = 'kdthobbit-eng/exam-hub-files';
    if (!preg_match('/^[0-9a-f]{40}$/', $sha)) {
        $api = wp_remote_get("https://api.github.com/repos/$repo/commits/main", ['timeout' => 20, 'headers' => ['User-Agent' => 'exam-hub-updater', 'Accept' => 'application/vnd.github+json']]);
        $j = json_decode(wp_remote_retrieve_body($api), true);
        $sha = is_array($j) && isset($j['sha']) ? $j['sha'] : '';
    }
    if (!preg_match('/^[0-9a-f]{40}$/', $sha)) return 'GitHub से commit नहीं मिला';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $zip = download_url("https://raw.githubusercontent.com/$repo/$sha/typing-site/examhub-pro.zip", 90);
    if (is_wp_error($zip)) return 'Download fail: ' . $zip->get_error_message();
    $tmp = WP_CONTENT_DIR . '/upgrade/ehp-' . time();
    wp_mkdir_p($tmp);
    $z = new ZipArchive();
    if ($z->open($zip) !== true) { @unlink($zip); return 'Zip नहीं खुला'; }
    $z->extractTo($tmp); $z->close(); @unlink($zip);
    if (!file_exists("$tmp/examhub-pro/style.css") || !file_exists("$tmp/examhub-pro/functions.php")) { eh_rrmdir($tmp); return 'Zip में theme नहीं मिली'; }
    $T = get_theme_root();
    eh_rrmdir("$T/examhub-pro.old");
    if (!@rename("$T/examhub-pro", "$T/examhub-pro.old")) { eh_rrmdir($tmp); return 'Theme folder rename नहीं हुआ (permission)'; }
    if (!@rename("$tmp/examhub-pro", "$T/examhub-pro")) { @rename("$T/examhub-pro.old", "$T/examhub-pro"); eh_rrmdir($tmp); return 'नई theme move नहीं हुई'; }
    eh_rrmdir($tmp);
    update_option('eh_deployed_sha', $sha, false);
    delete_option('eh_rewrite_ver');
    if (function_exists('opcache_reset')) @opcache_reset();
    return 'OK ' . substr($sha, 0, 7);
}

add_action('admin_post_eh_selfupdate', function () {
    if (!current_user_can('manage_options')) wp_die('Not allowed');
    check_admin_referer('eh_selfupdate');
    $r = eh_self_update(isset($_POST['sha']) ? sanitize_text_field(wp_unslash($_POST['sha'])) : '');
    wp_safe_redirect(admin_url('admin.php?page=eh-settings&ehu=' . rawurlencode($r)));
    exit;
});

add_action('eh_settings_after', function () {
    $msg = isset($_GET['ehu']) ? sanitize_text_field(wp_unslash($_GET['ehu'])) : '';
    echo '<hr><h2>Theme Update (GitHub)</h2>';
    if ($msg) echo '<div class="notice ' . (strpos($msg, 'OK') === 0 ? 'notice-success' : 'notice-error') . '"><p id="eh-upd-msg">' . esc_html($msg) . '</p></div>';
    echo '<p>अभी लगी version: <code id="eh-cur-sha">' . esc_html(substr((string) get_option('eh_deployed_sha', '-'), 0, 7)) . '</code></p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="eh_selfupdate">';
    wp_nonce_field('eh_selfupdate');
    echo '<input name="sha" id="eh-sha" class="regular-text code" placeholder="commit SHA (खाली = latest)"> ';
    submit_button('Update theme now', 'primary', 'submit', false, ['id' => 'eh-upd-btn']);
    echo '</form>';
});
