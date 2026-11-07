<?php
if (!defined('ABSPATH')) exit;

/* Custom Login / New User pages: /login/ and /signup/ */
add_action('init', function () {
    add_rewrite_rule('^login/?$', 'index.php?eh_view=login', 'top');
    add_rewrite_rule('^signup/?$', 'index.php?eh_view=signup', 'top');
});

function eh_login_page_url($redirect = '') { return add_query_arg($redirect ? ['redirect_to' => rawurlencode($redirect)] : [], home_url('/login/')); }
function eh_signup_page_url() { return home_url('/signup/'); }

/* Normal users ke liye apna login page; admin wp-login.php direct use kar sakta hai */
add_filter('login_url', function ($url, $redirect) {
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) return $url;
    return eh_login_page_url($redirect);
}, 10, 2);
add_filter('register_url', function () { return eh_signup_page_url(); });

function eh_safe_redirect_target() {
    $r = isset($_REQUEST['redirect_to']) ? rawurldecode(wp_unslash($_REQUEST['redirect_to'])) : '';
    $r = wp_validate_redirect($r, home_url('/my-dashboard/'));
    return $r ?: home_url('/my-dashboard/');
}

/* Form handling */
add_action('template_redirect', function () {
    $view = get_query_var('eh_view');
    if (!in_array($view, ['login', 'signup'], true)) return;
    if (is_user_logged_in()) { wp_safe_redirect(home_url('/my-dashboard/')); exit; }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!wp_verify_nonce($_POST['_eh'] ?? '', 'eh_auth')) { $GLOBALS['eh_auth_error'] = 'Page expired. Please try again.'; return; }

    $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . 'auth'), 0, 16);
    $n = (int) get_transient('eh_auth_' . $ip);
    if ($n > 20) { $GLOBALS['eh_auth_error'] = 'Too many attempts. Please try later.'; return; }
    set_transient('eh_auth_' . $ip, $n + 1, 15 * MINUTE_IN_SECONDS);

    if ($view === 'login') {
        $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
        $pw = (string) wp_unslash($_POST['pw'] ?? '');
        if (preg_match('/^[6-9]\d{9}$/', $id)) {
            $u = get_users(['meta_key' => 'eh_mobile', 'meta_value' => $id, 'number' => 1]);
            if ($u) $id = $u[0]->user_login;
        }
        $user = wp_signon(['user_login' => $id, 'user_password' => $pw, 'remember' => true], is_ssl());
        if (is_wp_error($user)) { $GLOBALS['eh_auth_error'] = 'Wrong email/mobile or password.'; return; }
        wp_safe_redirect(eh_safe_redirect_target()); exit;
    }

    // signup
    $fn = sanitize_text_field(wp_unslash($_POST['fn'] ?? ''));
    $ln = sanitize_text_field(wp_unslash($_POST['ln'] ?? ''));
    $em = sanitize_email(wp_unslash($_POST['em'] ?? ''));
    $mo = preg_replace('/\D/', '', $_POST['mo'] ?? '');
    $pw = (string) wp_unslash($_POST['pw'] ?? '');
    $err = '';
    if ($fn === '') $err = 'Enter your name.';
    elseif (!is_email($em)) $err = 'Enter a valid email.';
    elseif ($mo !== '' && !preg_match('/^[6-9]\d{9}$/', $mo)) $err = 'Mobile number must be 10 digits.';
    elseif (strlen($pw) < 6) $err = 'Password must be at least 6 characters.';
    elseif (empty($_POST['agree'])) $err = 'Please accept the Terms and Privacy Policy.';
    elseif (email_exists($em)) $err = 'This email already has an account. log in.';
    elseif ($mo && get_users(['meta_key' => 'eh_mobile', 'meta_value' => $mo, 'number' => 1])) $err = 'This mobile number is already registered.';
    if ($err) { $GLOBALS['eh_auth_error'] = $err; return; }

    $uid = wp_insert_user([
        'user_login' => eh_unique_login($em), 'user_email' => $em, 'user_pass' => $pw,
        'first_name' => $fn, 'last_name' => $ln, 'display_name' => trim($fn . ' ' . $ln), 'role' => 'subscriber',
    ]);
    if (is_wp_error($uid)) { $GLOBALS['eh_auth_error'] = 'Could not create the account. Please try again.'; return; }
    if ($mo) update_user_meta($uid, 'eh_mobile', $mo);
    wp_set_current_user($uid); wp_set_auth_cookie($uid, true, is_ssl());
    wp_safe_redirect(home_url('/my-dashboard/?welcome=1')); exit;
}, 0);

function eh_unique_login($email) {
    $base = sanitize_user(strstr($email, '@', true), true) ?: 'user';
    $base = substr($base, 0, 40);
    $l = $base; $i = 1;
    while (username_exists($l)) $l = $base . $i++;
    return $l;
}

/* Google Sign-In (Google Identity Services) */
add_action('rest_api_init', function () {
    register_rest_route('eh/v1', '/google', ['methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function (WP_REST_Request $r) {
        $cid = trim((string) get_option('eh_google_client_id', ''));
        $tok = (string) ($r->get_param('credential') ?? '');
        if (!$cid || !$tok) return new WP_REST_Response(['ok' => false], 400);
        $res = wp_remote_get('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($tok), ['timeout' => 10]);
        if (is_wp_error($res)) return new WP_REST_Response(['ok' => false, 'msg' => 'Google se jud nahi paye'], 502);
        $d = json_decode(wp_remote_retrieve_body($res), true);
        if (empty($d['aud']) || $d['aud'] !== $cid || empty($d['email']) || ($d['email_verified'] ?? '') !== 'true') return new WP_REST_Response(['ok' => false], 403);
        $email = sanitize_email($d['email']);
        $user = get_user_by('email', $email);
        if (!$user) {
            $uid = wp_insert_user(['user_login' => eh_unique_login($email), 'user_email' => $email, 'user_pass' => wp_generate_password(24),
                'first_name' => sanitize_text_field($d['given_name'] ?? ''), 'last_name' => sanitize_text_field($d['family_name'] ?? ''),
                'display_name' => sanitize_text_field($d['name'] ?? strstr($email, '@', true)), 'role' => 'subscriber']);
            if (is_wp_error($uid)) return new WP_REST_Response(['ok' => false], 500);
            $user = get_user_by('id', $uid);
        }
        if (user_can($user, 'edit_posts')) return new WP_REST_Response(['ok' => false, 'msg' => 'Admin account ke liye wp-admin se login karein'], 403);
        update_user_meta($user->ID, 'eh_google_sub', sanitize_text_field($d['sub'] ?? ''));
        if (!empty($d['picture'])) update_user_meta($user->ID, 'eh_avatar', esc_url_raw($d['picture']));
        wp_set_current_user($user->ID); wp_set_auth_cookie($user->ID, true, is_ssl());
        return ['ok' => true, 'redirect' => eh_safe_redirect_target()];
    }]);
});

function eh_google_button() {
    $cid = trim((string) get_option('eh_google_client_id', ''));
    if (!$cid) return '';
    wp_enqueue_script('gsi', 'https://accounts.google.com/gsi/client', [], null, true);
    $rest = esc_url(rest_url('eh/v1/google'));
    $redir = esc_attr(eh_safe_redirect_target());
    ob_start(); ?>
    <div class="eh-google">
      <div id="g_id_onload" data-client_id="<?php echo esc_attr($cid); ?>" data-callback="ehGoogle" data-auto_prompt="false"></div>
      <div class="g_id_signin" data-type="standard" data-size="large" data-shape="rectangular" data-text="continue_with" data-logo_alignment="center" data-width="400"></div>
      <p class="eh-g-msg" id="eh-g-msg" hidden></p>
    </div>
    <script>function ehGoogle(r){fetch('<?php echo $rest; ?>?redirect_to=<?php echo rawurlencode($redir); ?>',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({credential:r.credential})}).then(function(x){return x.json()}).then(function(j){if(j.ok){location.href=j.redirect}else{var m=document.getElementById('eh-g-msg');m.textContent=j.msg||'Google login failed. Please try again.';m.hidden=false}})}</script>
    <?php return ob_get_clean();
}
