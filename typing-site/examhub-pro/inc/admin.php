<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page('Exam Hub', 'Exam Hub', 'manage_options', 'eh-settings', 'eh_settings_page', 'dashicons-keyboard', 3);
});

add_action('admin_init', function () {
    foreach (['brand', 'tagline', 'notice', 'tg_group', 'tg_channel', 'allowed_error', 'live_lang', 'exams_hidden', 'exams_extra'] as $k) {
        register_setting('eh_settings', 'eh_' . $k, ['sanitize_callback' => function ($v) use ($k) {
            if ($k === 'exams_extra') { $j = json_decode(wp_unslash($v), true); return is_array($j) ? wp_json_encode($j, JSON_UNESCAPED_UNICODE) : '[]'; }
            if (in_array($k, ['tg_group', 'tg_channel'], true)) return esc_url_raw($v);
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
    <div class="wrap"><h1>Exam Hub Typing — Settings</h1>
    <p>कुल टेस्ट दिए गए: <b><?php echo number_format_i18n($total); ?></b> · Exams: <b><?php echo count(eh_exams()); ?></b> · Passages: <b><?php echo count(eh_passages()); ?></b> (नया पैसेज: <a href="<?php echo admin_url('post-new.php?post_type=eh_passage'); ?>">Typing Passages → Add</a>)</p>
    <form method="post" action="options.php"><?php settings_fields('eh_settings'); ?>
    <table class="form-table">
    <?php
    $f('brand', 'Site name');
    $f('tagline', 'Tagline');
    $f('notice', 'Top notice bar', 'खाली छोड़ें तो default दिखेगा');
    $f('tg_channel', 'Telegram Channel link');
    $f('tg_group', 'Telegram Group link');
    $f('allowed_error', 'Allowed error %', 'Default 5');
    $f('live_lang', 'Live typing language', 'en / hi — खाली = दिन के हिसाब से बदलता रहेगा');
    $f('exams_hidden', 'Hidden exam slugs', 'comma से अलग');
    ?>
    <tr><th>Extra / edited exams (JSON)</th><td><textarea name="eh_exams_extra" rows="6" class="large-text code"><?php echo esc_textarea(get_option('eh_exams_extra', '[]')); ?></textarea>
    <p class="description">Example: [{"slug":"up-court-clerk-hindi","name":"UP Civil Court Clerk Hindi Typing","lang":"hi","unit":"words","target":300,"minutes":10,"cat":"court"}]</p></td></tr>
    </table><?php submit_button(); ?></form></div>
    <?php
}
