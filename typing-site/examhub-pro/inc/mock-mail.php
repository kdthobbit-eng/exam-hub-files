<?php
/* Super TET mock: one scorecard email per candidate, sent once after their first counted submit.
   Mail goes out from a separate Gmail over SMTP (App Password set by the owner in WP-admin), in small batches by cron,
   so the exam itself is never slowed down. Images: static parts are pre-made, the score parts are filled in with GD. */
if (!defined('ABSPATH')) exit;

define('EH_STM_MAIL_ROUND', 'm2');
define('EH_STM_MAIL_DAILY', 500);

function eh_stm_mail_opt() {
    $o = get_option('eh_stm_mail', []);
    return array_merge(['from' => '', 'pass' => '', 'on' => 0, 'day' => '', 'sent_today' => 0, 'from2' => '', 'pass2' => '', 'sent2' => 0, 'bad1' => '', 'bad2' => '', 'prio' => 'prateek tiwari'], is_array($o) ? $o : []);
}
function eh_stm_mail_meta() { return 'eh_stm_mail_' . EH_STM_MAIL_ROUND; }

/* ---------- data for one candidate ---------- */
function eh_stm_mail_data($uid) {
    global $wpdb; $t = eh_stm_table(); $r = eh_stm_round(EH_STM_MAIL_ROUND);
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE round = %s AND uid = %d AND submitted > 0 AND counted = 1 ORDER BY att DESC LIMIT 1", $r['id'], $uid), ARRAY_A);
    if (!$row) return null;
    $sc = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT score FROM $t WHERE round = %s AND submitted > 0 AND counted = 1", $r['id'])));
    $s = (int) $row['score']; $n = count($sc);
    $Q = eh_stm_paper($r); $a = json_decode($row['ans'] ?: '{}', true) ?: []; $secs = [];
    foreach (eh_stm_secmap($r) as $sec) { $c = 0; $w = 0; foreach ($sec[1] as $i) { if (!isset($a[$i])) continue; if (isset($Q[$i]) && $a[$i] === $Q[$i]['a']) $c++; else $w++; } $secs[] = ['q' => count($sec[1]), 'c' => $c, 'w' => $w]; }
    $c = (int) $row['correct']; $w = (int) $row['wrong'];
    $used = (int) ($row['used'] ?? 0) ?: max(0, (int) $row['submitted'] - (int) $row['started']);
    return ['name' => $row['name'], 'email' => $row['email'], 'roll' => eh_stm_roll((int) $row['uid']), 'att' => (int) $row['att'],
        'score' => $s, 'c' => $c, 'w' => $w, 'skip' => 120 - $c - $w, 'acc' => ($c + $w) ? round($c * 100 / ($c + $w)) : 0,
        'mins' => (int) round($used / 60), 'rank' => count(array_filter($sc, function ($v) use ($s) { return $v > $s; })) + 1, 'total' => $n,
        'pct' => $n ? round(count(array_filter($sc, function ($v) use ($s) { return $v <= $s; })) * 100 / $n, 2) : 0, 'secs' => $secs];
}

/* ---------- images ---------- */
function eh_stm_mail_img($which, $d) {
    $dir = EH_DIR . '/assets/mail/'; $P = json_decode(file_get_contents($dir . 'pos.json'), true)[$which];
    $im = imagecreatefrompng($dir . $which . '_tpl.png'); $F = $dir . 'Poppins-Bold.ttf';
    $col = function ($h) use ($im) { return imagecolorallocate($im, hexdec(substr($h, 1, 2)), hexdec(substr($h, 3, 2)), hexdec(substr($h, 5, 2))); };
    $txt = function ($id, $s, $size, $hex, $align = 'c') use ($im, $P, $F, $col) {
        if (!isset($P[$id])) return; [$x, $y, $w, $h] = $P[$id]; $b = imagettfbbox($size, 0, $F, $s);
        $tw = $b[2] - $b[0]; $th = $b[1] - $b[7];
        $tx = $align === 'l' ? $x : ($align === 'r' ? $x + $w - $tw : $x + ($w - $tw) / 2);
        imagettftext($im, $size, 0, (int) round($tx - $b[0]), (int) round($y + ($h + $th) / 2 - $b[1]), $col($hex), $F, $s);
    };
    $mix = function ($a, $b, $t) use ($im) {
        $p = function ($h, $i) { return hexdec(substr($h, 1 + $i * 2, 2)); };
        return imagecolorallocate($im, (int) ($p($a, 0) + ($p($b, 0) - $p($a, 0)) * $t), (int) ($p($a, 1) + ($p($b, 1) - $p($a, 1)) * $t), (int) ($p($a, 2) + ($p($b, 2) - $p($a, 2)) * $t));
    };
    if ($which === 'sc') {
        [$rx, $ry] = $P['ring']; $cx = $rx + 190; $cy = $ry + 190; $R1 = 176; $R0 = 144;
        $deg = (int) round(360 * max(0, min(360, $d['score'])) / 360);
        $cache = [];
        for ($py = $cy - $R1; $py <= $cy + $R1; $py++) for ($px = $cx - $R1; $px <= $cx + $R1; $px++) {
            $dx = $px - $cx; $dy = $py - $cy; $dist = sqrt($dx * $dx + $dy * $dy); if ($dist < $R0 || $dist > $R1) continue;
            $ang = fmod(rad2deg(atan2($dx, -$dy)) + 360, 360); if ($ang > $deg) continue;
            $k = (int) $ang; if (!isset($cache[$k])) $cache[$k] = $mix('#f5b53f', '#1d74dc', $k / 360);
            imagesetpixel($im, $px, $py, $cache[$k]);
        }
        if ($deg > 0) { $rc = ($R1 + $R0) / 2; $cw = $R1 - $R0;
            imagefilledellipse($im, $cx, (int) ($cy - $rc), $cw, $cw, $mix('#f5b53f', '#1d74dc', 0));
            $e = deg2rad($deg); imagefilledellipse($im, (int) round($cx + $rc * sin($e)), (int) round($cy - $rc * cos($e)), $cw, $cw, $mix('#f5b53f', '#1d74dc', min(359, $deg) / 360)); }
        $txt('rs', (string) $d['score'], 72, '#0f2a4d');
        $txt('rp', round($d['score'] * 100 / 360, 1) . '%', 21, '#6b7686');
        foreach (['tc' => $d['c'], 'tw' => $d['w'], 'ts' => $d['skip'], 'ta' => $d['acc'] . '%'] as $k => $v) $txt($k, (string) $v, 36, '#ffffff', 'l');
        foreach ([$d['roll'], (string) $d['att'], (string) ($d['c'] + $d['w']), (string) $d['mins']] as $k => $v) $txt('s' . $k, $v, 22, '#ffd277');
    } else {
        foreach ($d['secs'] as $k => $s) {
            if (!isset($P['bar' . $k])) continue; [$x, $y, $w, $h] = $P['bar' . $k]; $r = $h / 2; $cyb = $y + $r; $x1 = $x + $w;
            $gw = (int) round($w * $s['c'] / max(1, $s['q'])); $rw = (int) round($w * $s['w'] / max(1, $s['q']));
            for ($i = 0; $i < $gw + $rw && $i < $w; $i++) {
                $px = $x + $i; $dy = $r;
                if ($px < $x + $r) $dy = sqrt(max(0, $r * $r - pow($x + $r - $px, 2)));
                elseif ($px > $x1 - $r) $dy = sqrt(max(0, $r * $r - pow($px - ($x1 - $r), 2)));
                $c = $i < $gw ? $mix('#16a34a', '#4ade80', $gw ? $i / $gw : 0) : $mix('#f97316', '#ef4444', $rw ? ($i - $gw) / $rw : 0);
                imageline($im, $px, (int) round($cyb - $dy), $px, (int) round($cyb + $dy - 1), $c);
            }
            $txt('m' . $k, (string) ($s['c'] * 3 - $s['w']), 22, '#0f2a4d', 'r');
        }
    }
    ob_start(); imagejpeg($im, null, 90); imagedestroy($im); return ob_get_clean();
}

/* ---------- html ---------- */
function eh_stm_mail_html($d) {
    $img = function ($cid, $alt, $link = '', $rad = '0') {
        $i = '<img src="cid:' . $cid . '" width="600" alt="' . esc_attr($alt) . '" style="display:block;width:100%;height:auto;border:0;border-radius:' . $rad . '">';
        return $link ? '<a href="' . esc_url($link) . '">' . $i . '</a>' : $i;
    };
    $rk = $d['total'] ? 'आपकी अभी की रैंक <b style="color:#0f2a4d">' . $d['rank'] . ' / ' . $d['total'] . '</b> है और परसेंटाइल <b style="color:#0f2a4d">' . number_format($d['pct'], 2) . '</b>। ' : '';
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="padding:16px 0">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%">
<tr><td>' . $img('hd', 'UPSSSC ALERT — UP Super TET 2026 लाइव मॉक परिणाम', '', '16px 16px 0 0') . '</td></tr>
<tr><td style="padding:24px 22px 8px;font-family:Arial,\'Noto Sans Devanagari\',sans-serif">
<p style="margin:0 0 10px;font-size:21px;font-weight:bold;color:#0f2a4d">नमस्ते ' . esc_html($d['name']) . ' जी,</p>
<p style="margin:0;font-size:16px;line-height:1.8;color:#1f2937">UP Super TET 2026 लाइव मॉक टेस्ट में भाग लेने के लिए <b style="color:#0f2a4d">UPSSSC ALERT</b> की ओर से आपका हार्दिक धन्यवाद। आपकी परीक्षा सफलतापूर्वक सबमिट हो चुकी है। आपका विस्तृत स्कोरकार्ड नीचे है।</p></td></tr>
<tr><td style="padding:14px 0 0">' . $img('sc', 'स्कोर ' . $d['score'] . ' / 360 · सही ' . $d['c'] . ' · गलत ' . $d['w'] . ' · छोड़े ' . $d['skip']) . '</td></tr>
<tr><td>' . $img('se', 'सेक्शन-वार प्रदर्शन') . '</td></tr>
<tr><td style="padding:10px 0 0">' . $img('ct', 'अपनी लाइव रैंक और परसेंटाइल देखें', home_url('/super-tet-mock/'), '16px') . '</td></tr>
<tr><td style="padding:12px 0 0">' . $img('tg', 'टेलीग्राम पर जुड़ें', 'https://t.me/UpssscAlert', '16px') . '</td></tr>
<tr><td style="padding:12px 0 0">' . $img('ft', 'टीम UPSSSC ALERT · XL Study', '', '0 0 16px 16px') . '</td></tr>
<tr><td style="padding:14px 22px;font-family:Arial,sans-serif;font-size:11.5px;line-height:1.7;color:#6b7280;text-align:center">यह ईमेल आपको इसलिए मिला है क्योंकि आपने xlstudy.in पर UP Super TET 2026 लाइव मॉक टेस्ट दिया है। आगे ऐसे ईमेल नहीं चाहिए तो “STOP” लिखकर उत्तर दें।<br><a href="' . esc_url(home_url('/')) . '" style="color:#1d4ed8">xlstudy.in</a> · <a href="https://t.me/UpssscAlert" style="color:#1d4ed8">Telegram</a></td></tr>
</table></td></tr></table>';
}
function eh_stm_mail_text($d) {
    return "नमस्ते {$d['name']} जी,\n\nUP Super TET 2026 लाइव मॉक टेस्ट में भाग लेने के लिए UPSSSC ALERT की ओर से धन्यवाद।\n\nस्कोर: {$d['score']} / 360 · प्रयास संख्या: {$d['att']}\nसही {$d['c']} · गलत {$d['w']} · छोड़े {$d['skip']}\n"
        . "\nलाइव रैंक और परसेंटाइल: " . home_url('/super-tet-mock/') . "\nटेलीग्राम: https://t.me/UpssscAlert\n\nटीम UPSSSC ALERT";
}

/* ---------- send ---------- */
/* Sends per account in the last 24 hours (Gmail counts a rolling 24h window), read from each candidate's record. */
function eh_stm_mail_24h() {
    global $wpdb; static $c = null; if ($c !== null) return $c;
    $c = [1 => 0, 2 => 0]; $since = time() - DAY_IN_SECONDS;
    foreach ($wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key IN (%s, 'eh_stm_rem_m2') AND meta_value LIKE 'sent:%%'", eh_stm_mail_meta())) as $v) {
        $p = explode(':', $v); if ((int) ($p[1] ?? 0) >= $since) $c[(int) ($p[2] ?? 1) === 2 ? 2 : 1]++;
    }
    return $c;
}
/* which sender account to use now: 1, 2 or 0 (none free right now) */
function eh_stm_mail_acct($o, $c = null) {
    $c = $c ?: eh_stm_mail_24h(); $now = time();
    foreach ([1 => ['from', 'pass'], 2 => ['from2', 'pass2']] as $a => $k)
        if ($o[$k[0]] && $o[$k[1]] && (int) ($o['pause' . $a] ?? 0) < $now && $c[$a] < EH_STM_MAIL_DAILY) return $a;
    return 0;
}
function eh_stm_mail_send($d, $to = '', $acct = 1) {
    $o = eh_stm_mail_opt();
    if ($acct == 2) { $o['from'] = $o['from2']; $o['pass'] = $o['pass2']; }
    if (!$o['from'] || !$o['pass']) return 'Mail settings खाली हैं';
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php'; require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php'; require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    $m = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->isSMTP(); $m->Host = 'smtp.gmail.com'; $m->SMTPAuth = true; $m->Username = $o['from']; $m->Password = str_replace(' ', '', $o['pass']);
        $m->SMTPSecure = 'tls'; $m->Port = 587; $m->Timeout = 20; $m->CharSet = 'UTF-8';
        $m->setFrom($o['from'], 'UPSSSC ALERT'); $m->addReplyTo($o['from'], 'UPSSSC ALERT'); $m->addAddress($to ?: $d['email'], $d['name']);
        $m->Subject = '🏆 आपका स्कोरकार्ड तैयार है — UP Super TET 2026 लाइव मॉक | UPSSSC ALERT';
        $dir = EH_DIR . '/assets/mail/';
        foreach (['hd', 'ct', 'tg', 'ft'] as $k) $m->addEmbeddedImage($dir . $k . '.jpg', $k, $k . '.jpg', 'base64', 'image/jpeg');
        foreach (['sc', 'se'] as $k) $m->addStringEmbeddedImage(eh_stm_mail_img($k, $d), $k, $k . '.jpg', 'base64', 'image/jpeg');
        $m->isHTML(true); $m->Body = eh_stm_mail_html($d); $m->AltBody = eh_stm_mail_text($d);
        $m->send(); return true;
    } catch (\Throwable $e) { return $m->ErrorInfo ?: $e->getMessage(); }
}

/* ---------- queue: every minute, a few candidates who submitted and have not been mailed ---------- */
add_filter('cron_schedules', function ($s) { $s['eh_min'] = ['interval' => 60, 'display' => 'Every minute']; return $s; });
add_action('init', function () { if (!wp_next_scheduled('eh_stm_mail_tick')) wp_schedule_event(time() + 60, 'eh_min', 'eh_stm_mail_tick'); });
function eh_stm_mail_pending($limit = 0) {
    global $wpdb; $t = eh_stm_table(); $mk = eh_stm_mail_meta();
    $sql = $wpdb->prepare("SELECT DISTINCT m.uid FROM $t m LEFT JOIN {$wpdb->usermeta} um ON um.user_id = m.uid AND um.meta_key = %s
        WHERE m.round = %s AND m.submitted > 0 AND m.counted = 1 AND (um.meta_value IS NULL OR um.meta_value LIKE 'fail:%%') ORDER BY m.submitted ASC", $mk, EH_STM_MAIL_ROUND);
    $ids = array_map('intval', $wpdb->get_col($sql));
    $ids = array_values(array_filter($ids, function ($u) use ($mk) { $v = get_user_meta($u, $mk, true); return !$v || (strpos($v, 'fail:') === 0 && (int) substr($v, 5) < 3); }));
    /* names/emails to mail first */
    $pr = array_filter(array_map('trim', explode(',', strtolower(eh_stm_mail_opt()['prio'] ?? 'prateek tiwari'))));
    if ($pr && $ids) {
        $first = $wpdb->get_col("SELECT DISTINCT uid FROM $t WHERE round = '" . esc_sql(EH_STM_MAIL_ROUND) . "' AND (" . implode(' OR ', array_map(function ($p) { $l = '%' . esc_sql($GLOBALS['wpdb']->esc_like($p)) . '%'; return "LOWER(name) LIKE '$l' OR LOWER(email) LIKE '$l'"; }, $pr)) . ")");
        $first = array_map('intval', $first);
        usort($ids, function ($a, $b) use ($first) { return (int) !in_array($a, $first, true) <=> (int) !in_array($b, $first, true); });
    }
    return $ids;
}
add_action('eh_stm_mail_tick', function () {
    $o = eh_stm_mail_opt(); if (!$o['on']) return;
    $c = eh_stm_mail_24h(); if (!eh_stm_mail_acct($o, $c)) return;
    if (get_transient('eh_stm_mail_lock')) return; set_transient('eh_stm_mail_lock', 1, 300);
    $mk = eh_stm_mail_meta(); $n = 0; $set = [];
    foreach (eh_stm_mail_pending() as $uid) {
        $ac = eh_stm_mail_acct(array_merge($o, $set), $c); if ($n >= 8 || !$ac) break;
        if (user_can($uid, 'manage_options')) { update_user_meta($uid, $mk, 'skip:admin'); continue; }
        $d = eh_stm_mail_data($uid); if (!$d || !is_email($d['email'])) { update_user_meta($uid, $mk, 'skip:noemail'); continue; }
        $prev = get_user_meta($uid, $mk, true); update_user_meta($uid, $mk, 'sending');
        $res = eh_stm_mail_send($d, '', $ac); $n++;
        if ($res === true) { update_user_meta($uid, $mk, 'sent:' . time() . ':' . $ac); $c[$ac]++; continue; }
        $e = (string) $res; $set['last_err'] = 'Gmail ' . $ac . ': ' . mb_substr($e, 0, 200);
        $restore = function () use ($uid, $mk, $prev) { if ($prev) update_user_meta($uid, $mk, $prev); else delete_user_meta($uid, $mk); };
        if (stripos($e, 'authenticate') !== false || stripos($e, 'Username and Password') !== false) { $set['pause' . $ac] = time() + 6 * HOUR_IN_SECONDS; $restore(); }
        elseif (preg_match('/limit|quota|550|421|rate/i', $e)) { $set['pause' . $ac] = time() + HOUR_IN_SECONDS; $restore(); }
        else update_user_meta($uid, $mk, 'fail:' . (((strpos((string) $prev, 'fail:') === 0) ? (int) substr($prev, 5) : 0) + 1));
    }
    eh_stm_rem_tick($o, $c, $set);
    if ($set) { $fresh = eh_stm_mail_opt(); update_option('eh_stm_mail', array_merge($fresh, $set), false); }
    delete_transient('eh_stm_mail_lock');
});
function eh_stm_mail_counts() {
    global $wpdb; $mk = eh_stm_mail_meta();
    $sent = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE 'sent:%%'", $mk));
    $fail = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE 'fail:%%'", $mk));
    return ['sent' => $sent, 'fail' => $fail, 'pending' => count(eh_stm_mail_pending())];
}

/* ---------- WP-admin: Mail Settings ---------- */
add_action('admin_menu', function () {
    add_submenu_page('eh-stm', 'Mail Settings', '📧 Mail Settings', 'manage_options', 'eh-stm-mail', 'eh_stm_mail_page');
}, 20);
add_action('admin_post_eh_stm_mail', function () {
    if (!current_user_can('manage_options')) wp_die('No'); check_admin_referer('eh_stm_mail');
    $o = eh_stm_mail_opt(); $do = sanitize_key($_POST['do'] ?? 'save'); $msg = '';
    if ($do === 'save') {
        $o['from'] = sanitize_email(wp_unslash($_POST['from'] ?? ''));
        $p = trim(wp_unslash($_POST['pass'] ?? '')); if ($p !== '') $o['pass'] = $p;
        $o['from2'] = sanitize_email(wp_unslash($_POST['from2'] ?? ''));
        $p = trim(wp_unslash($_POST['pass2'] ?? '')); if ($p !== '') $o['pass2'] = $p;
        $o['pause1'] = $o['pause2'] = 0;
        $msg = 'saved';
    } elseif ($do === 'test') {
        $me = wp_get_current_user(); $d = eh_stm_mail_data($me->ID) ?: ['name' => $me->display_name ?: 'Kapil', 'email' => $me->user_email, 'roll' => 'XL26000001', 'att' => 2, 'score' => 212, 'c' => 79, 'w' => 25, 'skip' => 16, 'acc' => 76, 'mins' => 108, 'rank' => 12, 'total' => 340, 'pct' => 96.76,
            'secs' => [['q' => 25, 'c' => 15, 'w' => 6], ['q' => 5, 'c' => 4, 'w' => 1], ['q' => 30, 'c' => 20, 'w' => 6], ['q' => 8, 'c' => 5, 'w' => 2], ['q' => 16, 'c' => 10, 'w' => 3], ['q' => 8, 'c' => 6, 'w' => 1], ['q' => 8, 'c' => 6, 'w' => 2], ['q' => 8, 'c' => 6, 'w' => 1], ['q' => 8, 'c' => 4, 'w' => 2], ['q' => 4, 'c' => 3, 'w' => 1]]];
        $to = sanitize_email(wp_unslash($_POST['to'] ?? '')) ?: $me->user_email;
        $res = eh_stm_mail_send($d, $to, (int) ($_POST['acct'] ?? 1) === 2 ? 2 : 1); $msg = $res === true ? 'test_ok' : 'test_fail'; if ($res !== true) $o['last_err'] = mb_substr((string) $res, 0, 200);
    } elseif ($do === 'on') { $o['on'] = 1; $msg = 'on'; }
    elseif ($do === 'off') { $o['on'] = 0; $msg = 'off'; }
    update_option('eh_stm_mail', $o, false);
    wp_safe_redirect(admin_url('admin.php?page=eh-stm-mail&m=' . $msg)); exit;
});
function eh_stm_mail_page() {
    $o = eh_stm_mail_opt(); $c = eh_stm_mail_counts(); $m = sanitize_key($_GET['m'] ?? '');
    $note = ['saved' => ['success', 'Settings save हो गईं।'], 'test_ok' => ['success', 'Test mail भेज दिया — inbox देखें।'], 'test_fail' => ['error', 'Test mail नहीं गया: ' . esc_html($o['last_err'] ?? '')],
        'on' => ['success', 'Mail चालू — हर candidate को पहले submit के बाद एक बार mail जाएगा।'], 'off' => ['warning', 'Mail बंद कर दिया।']][$m] ?? null;
    $f = function ($do, $label, $cls = 'button') { return '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin-right:8px">' . wp_nonce_field('eh_stm_mail', '_wpnonce', true, false) . '<input type="hidden" name="action" value="eh_stm_mail"><input type="hidden" name="do" value="' . $do . '"><button class="' . $cls . '">' . $label . '</button></form>'; };
    echo '<div class="wrap"><h1>📧 Mock Mail Settings</h1>';
    if ($note) echo '<div class="notice notice-' . $note[0] . '"><p>' . $note[1] . '</p></div>';
    echo '<div style="display:flex;gap:12px;margin:14px 0">';
    foreach ([['स्थिति', $o['on'] ? '🟢 चालू' : '⚪ बंद'], ['भेजे गए', $c['sent']], ['बाकी (queue)', $c['pending']], ['फेल', $c['fail']], ['Gmail 1 (24 घंटे)', eh_stm_mail_24h()[1] . ' / ' . EH_STM_MAIL_DAILY . ((int) ($o['pause1'] ?? 0) > time() ? ' ⏸' : '')], ['Gmail 2 (24 घंटे)', eh_stm_mail_24h()[2] . ' / ' . EH_STM_MAIL_DAILY . ((int) ($o['pause2'] ?? 0) > time() ? ' ⏸' : '')]] as $k)
        echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:12px 18px;min-width:110px"><div style="font-size:22px;font-weight:700">' . $k[1] . '</div><div style="color:#646970">' . $k[0] . '</div></div>';
    echo '</div>';
    if (!empty($o['last_err'])) echo '<p style="color:#b32d2e">आखिरी error: ' . esc_html($o['last_err']) . '</p>';
    echo '<h2>1. Gmail और App Password</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">' . wp_nonce_field('eh_stm_mail', '_wpnonce', true, false) . '<input type="hidden" name="action" value="eh_stm_mail"><input type="hidden" name="do" value="save">
<table class="form-table"><tr><th>भेजने वाला Gmail</th><td><input type="email" name="from" class="regular-text" value="' . esc_attr($o['from']) . '" placeholder="example@gmail.com"></td></tr>
<tr><th>App Password (16 अक्षर)</th><td><input type="password" name="pass" class="regular-text" autocomplete="new-password" placeholder="' . ($o['pass'] ? '•••• सेव है — बदलना हो तो नया डालें' : 'abcd efgh ijkl mnop') . '"><p class="description">Gmail → Security → 2-Step Verification ON → App passwords से बनाएँ। यह पासवर्ड यहाँ दोबारा नहीं दिखेगा।</p></td></tr>
<tr><th colspan="2" style="padding-top:18px">दूसरा Gmail (पहले की आज की लिमिट पूरी होने पर इससे जाएगा)</th></tr>
<tr><th>दूसरा Gmail</th><td><input type="email" name="from2" class="regular-text" value="' . esc_attr($o['from2']) . '" placeholder="second@gmail.com"></td></tr>
<tr><th>दूसरे का App Password</th><td><input type="password" name="pass2" class="regular-text" autocomplete="new-password" placeholder="' . ($o['pass2'] ? '•••• सेव है — बदलना हो तो नया डालें' : 'abcd efgh ijkl mnop') . '"></td></tr></table>
<p><button class="button button-primary">Save</button></p></form>';
    echo '<h2>2. Test mail</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">' . wp_nonce_field('eh_stm_mail', '_wpnonce', true, false) . '<input type="hidden" name="action" value="eh_stm_mail"><input type="hidden" name="do" value="test">
<input type="email" name="to" class="regular-text" placeholder="' . esc_attr(wp_get_current_user()->user_email) . '"> <select name="acct"><option value="1">Gmail 1 से</option><option value="2">Gmail 2 से</option></select> <button class="button">Test mail भेजो</button></form>';
    echo '<h2>3. चालू / बंद</h2><p>चालू होने पर हर candidate को <b>पहली बार submit करने के बाद एक ही बार</b> mail जाता है (हर मिनट 8 तक, हर Gmail से दिन में ' . EH_STM_MAIL_DAILY . ' तक — पहले Gmail 1, फिर Gmail 2)। एडमिन को mail नहीं जाता।</p>'
        . ($o['on'] ? $f('off', '⏸ Mail बंद करो') : $f('on', '▶️ Mail चालू करो', 'button button-primary')) . '</div>';
}

/* ---------- Mock 2 reminder: count only (read-only) ---------- */
function eh_stm_remind_list() {
    global $wpdb; $t = eh_stm_table();
    $ids = $wpdb->get_col("SELECT DISTINCT uid FROM $t WHERE round = 'm1' AND submitted > 0 AND uid NOT IN (SELECT uid FROM $t WHERE round = 'm2' AND started > 0)");
    $out = [];
    foreach ($ids as $id) { $u = get_userdata((int) $id); if ($u && !user_can($u, 'manage_options') && is_email($u->user_email)) $out[] = (int) $id; }
    return $out;
}
function eh_stm_remind_report() {
    global $wpdb; $t = eh_stm_table();
    $m1 = (int) $wpdb->get_var("SELECT COUNT(DISTINCT uid) FROM $t WHERE round = 'm1' AND submitted > 0");
    $both = (int) $wpdb->get_var("SELECT COUNT(DISTINCT uid) FROM $t WHERE round = 'm1' AND submitted > 0 AND uid IN (SELECT uid FROM $t WHERE round = 'm2' AND started > 0)");
    $l = eh_stm_remind_list(); $c = eh_stm_mail_24h();
    return "📊 Reminder ginti\nMock 1 submit: $m1\nजिन्होंने Mock 2 दे दिया/शुरू किया: $both\nबचे (admin हटाकर, सही email): " . count($l)
        . "\n\nपिछले 24 घंटे में भेजे गए: Gmail 1 = {$c[1]}, Gmail 2 = {$c[2]} (हर एक की हद " . EH_STM_MAIL_DAILY . ")

" . eh_stm_rem_stats();
}


/* ---------- Mock 2 reminder: one mail to Mock 1 takers who have not started Mock 2 (runs only while Mock 2 is open) ---------- */
function eh_stm_rem_queue() {
    global $wpdb; $t = eh_stm_table();
    $ids = $wpdb->get_col("SELECT uid FROM $t WHERE round = 'm1' AND submitted > 0 GROUP BY uid ORDER BY MAX(score) DESC");
    $ids = array_values(array_filter(array_map('intval', $ids), function ($u) { $v = get_user_meta($u, 'eh_stm_rem_m2', true); return !$v || (strpos($v, 'fail:') === 0 && (int) substr($v, 5) < 3); }));
    $pr = array_filter(array_map('trim', explode(',', strtolower(eh_stm_mail_opt()['prio'] ?? ''))));
    if ($pr && $ids) {
        $first = array_map('intval', $wpdb->get_col("SELECT DISTINCT uid FROM $t WHERE round = 'm1' AND (" . implode(' OR ', array_map(function ($p) { $l = '%' . esc_sql($GLOBALS['wpdb']->esc_like($p)) . '%'; return "LOWER(name) LIKE '$l' OR LOWER(email) LIKE '$l'"; }, $pr)) . ")"));
        usort($ids, function ($a, $b) use ($first) { return (int) !in_array($a, $first, true) <=> (int) !in_array($b, $first, true); });
        return [$ids, $first];
    }
    return [$ids, []];
}
function eh_stm_rem_html($name) {
    $img = function ($cid, $alt, $link = '', $rad = '0') {
        $i = '<img src="cid:' . $cid . '" width="600" alt="' . esc_attr($alt) . '" style="display:block;width:100%;height:auto;border:0;border-radius:' . $rad . '">';
        return $link ? '<a href="' . esc_url($link) . '">' . $i . '</a>' : $i;
    };
    $u = home_url('/super-tet-mock/');
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="padding:16px 0">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%">
<tr><td>' . $img('rh', 'UPSSSC ALERT — UP Super TET 2026 मॉक-2 अभी लाइव', '', '16px 16px 0 0') . '</td></tr>
<tr><td style="padding:24px 22px 8px;font-family:Arial,\'Noto Sans Devanagari\',sans-serif">
<p style="margin:0 0 10px;font-size:21px;font-weight:bold;color:#0f2a4d">नमस्ते ' . esc_html($name) . ' जी,</p>
<p style="margin:0;font-size:16px;line-height:1.8;color:#1f2937">UP Super TET 2026 का <b style="color:#0f2a4d">लाइव मॉक टेस्ट – 2</b> अभी चालू है और आज रात 11:59 बजे तक खुला है। अगर आपने अभी तक अटेम्प्ट नहीं किया है, तो अभी अटेम्प्ट कर लें।</p></td></tr>
<tr><td style="padding:14px 0 0">' . $img('rc', 'अभी मॉक दें', $u, '16px') . '</td></tr>
<tr><td style="padding:18px 22px 4px;font-family:Arial,\'Noto Sans Devanagari\',sans-serif;font-size:15px;line-height:1.7;color:#1f2937">From –<br><b style="color:#0f2a4d;font-size:17px">प्रतीक तिवारी</b><br>Owner – UPSSSC ALERT</td></tr>
<tr><td style="padding:12px 0 0">' . $img('tg', 'टेलीग्राम पर जुड़ें', 'https://t.me/UpssscAlert', '16px') . '</td></tr>
<tr><td style="padding:12px 0 0">' . $img('ft', 'टीम UPSSSC ALERT · XL Study', '', '0 0 16px 16px') . '</td></tr>
<tr><td style="padding:14px 22px;font-family:Arial,sans-serif;font-size:11.5px;line-height:1.7;color:#6b7280;text-align:center">यह ईमेल आपको इसलिए मिला है क्योंकि आपने xlstudy.in पर UP Super TET 2026 लाइव मॉक टेस्ट – 1 दिया था। आगे ऐसे ईमेल नहीं चाहिए तो “STOP” लिखकर उत्तर दें।<br><a href="' . esc_url(home_url('/')) . '" style="color:#1d4ed8">xlstudy.in</a> · <a href="https://t.me/UpssscAlert" style="color:#1d4ed8">Telegram</a></td></tr>
</table></td></tr></table>';
}
function eh_stm_rem_send($name, $to, $acct) {
    $o = eh_stm_mail_opt();
    if ($acct == 2) { $o['from'] = $o['from2']; $o['pass'] = $o['pass2']; }
    if (!$o['from'] || !$o['pass']) return 'Mail settings खाली हैं';
    require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php'; require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php'; require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    $m = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->isSMTP(); $m->Host = 'smtp.gmail.com'; $m->SMTPAuth = true; $m->Username = $o['from']; $m->Password = str_replace(' ', '', $o['pass']);
        $m->SMTPSecure = 'tls'; $m->Port = 587; $m->Timeout = 20; $m->CharSet = 'UTF-8';
        $m->setFrom($o['from'], 'UPSSSC ALERT'); $m->addReplyTo($o['from'], 'UPSSSC ALERT'); $m->addAddress($to, $name);
        $m->Subject = '⏰ लाइव मॉक टेस्ट – 2 चालू है, अभी अटेम्प्ट करें — UP Super TET 2026 | UPSSSC ALERT';
        $dir = EH_DIR . '/assets/mail/';
        foreach (['rh', 'rc', 'tg', 'ft'] as $k) $m->addEmbeddedImage($dir . $k . '.jpg', $k, $k . '.jpg', 'base64', 'image/jpeg');
        $m->isHTML(true); $m->Body = eh_stm_rem_html($name);
        $m->AltBody = "नमस्ते $name जी,\n\nUP Super TET 2026 का लाइव मॉक टेस्ट – 2 अभी चालू है और आज रात 11:59 बजे तक खुला है। अगर आपने अभी तक अटेम्प्ट नहीं किया है, तो अभी अटेम्प्ट कर लें।\n\nमॉक दें: " . home_url('/super-tet-mock/') . "\n\nFrom –\nप्रतीक तिवारी\nOwner – UPSSSC ALERT";
        $m->send(); return true;
    } catch (\Throwable $e) { return $m->ErrorInfo ?: $e->getMessage(); }
}
function eh_stm_rem_tick($o, &$c, &$set) {
    $rem = get_option('eh_stm_rem', []); if (empty($rem['on'])) return;
    $r = eh_stm_round('m2'); if (!$r || time() >= (int) $r['end_ts']) return;
    global $wpdb; $t = eh_stm_table(); $mk = 'eh_stm_rem_m2'; $n = 0;
    [$ids, $first] = eh_stm_rem_queue();
    foreach ($ids as $uid) {
        $ac = eh_stm_mail_acct(array_merge($o, $set), $c); if ($n >= 8 || !$ac) break;
        $u = get_userdata($uid); $isFirst = in_array($uid, $first, true);
        if (!$u || !is_email($u->user_email)) { update_user_meta($uid, $mk, 'skip:noemail'); continue; }
        if (!$isFirst && user_can($u, 'manage_options')) { update_user_meta($uid, $mk, 'skip:admin'); continue; }
        if (!$isFirst && $wpdb->get_var($wpdb->prepare("SELECT 1 FROM $t WHERE round = 'm2' AND uid = %d AND started > 0 LIMIT 1", $uid))) { update_user_meta($uid, $mk, 'skip:gave'); continue; }
        $name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $t WHERE round = 'm1' AND uid = %d AND name <> '' ORDER BY att DESC LIMIT 1", $uid)) ?: ($u->display_name ?: 'अभ्यर्थी');
        $prev = get_user_meta($uid, $mk, true); update_user_meta($uid, $mk, 'sending');
        $res = eh_stm_rem_send($name, $u->user_email, $ac); $n++;
        if ($res === true) { update_user_meta($uid, $mk, 'sent:' . time() . ':' . $ac); $c[$ac]++; continue; }
        $e = (string) $res; $set['last_err'] = 'Gmail ' . $ac . ' (reminder): ' . mb_substr($e, 0, 200);
        if ($prev) update_user_meta($uid, $mk, $prev); else delete_user_meta($uid, $mk);
        if (stripos($e, 'authenticate') !== false || stripos($e, 'Username and Password') !== false) $set['pause' . $ac] = time() + 6 * HOUR_IN_SECONDS;
        elseif (preg_match('/limit|quota|550|421|rate/i', $e)) $set['pause' . $ac] = time() + HOUR_IN_SECONDS;
        else update_user_meta($uid, $mk, 'fail:' . (((strpos((string) $prev, 'fail:') === 0) ? (int) substr($prev, 5) : 0) + 1));
    }
}
function eh_stm_rem_stats() {
    global $wpdb;
    $g = function ($like) use ($wpdb) { return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'eh_stm_rem_m2' AND meta_value LIKE %s", $like)); };
    return "⏰ Reminder mail: " . (empty(get_option('eh_stm_rem', [])['on']) ? 'बंद' : 'चालू') . "\nभेजे: " . $g('sent:%') . " · Mock 2 दे चुके (छोड़े): " . $g('skip:gave%') . " · फेल: " . $g('fail:%') . "\nबाकी: " . count(eh_stm_rem_queue()[0]);
}
