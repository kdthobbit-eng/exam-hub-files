<?php
/* Super TET live mock — TCS iON style computer based test with login, server timer, rank and percentile. Unlisted, noindex. */
if (!defined('ABSPATH')) exit;
header('X-Robots-Tag: noindex, nofollow', true);
nocache_headers();
$eh_in = is_user_logged_in();
$eh_admin = $eh_in && current_user_can('manage_options') && isset($_GET['admin']);
$eh_self = home_url('/super-tet-mock/');
$eh_cfg = [
    'rest' => esc_url_raw(rest_url('eh/v1/stm/')), 'nonce' => $eh_in ? wp_create_nonce('wp_rest') : '',
    'in' => $eh_in, 'admin' => $eh_admin, 'now' => time(),
    'round' => eh_stm_round_out(eh_stm_round()),
    'login' => eh_login_page_url($eh_self), 'signup' => add_query_arg('redirect_to', rawurlencode($eh_self), home_url('/signup/')),
    'logo' => get_template_directory_uri() . '/assets/upsssc-alert-logo.svg',
    'logout' => wp_logout_url($eh_self), 'tgc' => eh_opt('tg_channel'), 'tgg' => eh_opt('tg_group'), 'tgj' => eh_opt('tg_join'),
    'wa' => eh_wa_url('Hello, I want to join the Acharya 2.0 (Super TET) batch.'), 'bookwa' => 'https://wa.me/918726821839?text=' . rawurlencode('UP SUPER TET BOOK order'),
    'booktg' => 'https://t.me/Booksalert2026', 'book' => get_template_directory_uri() . '/assets/book-supertet.jpg',
];
$eh_logo = esc_url($eh_cfg['logo']);
$eh_hd = function ($sub = 'Online Examination', $right = '', $out = false, $home = true) use ($eh_logo, $eh_in, $eh_cfg) { ?>
 <header class="hd"><div class="brand"><?php if ($home): ?><a class="hm" href="<?php echo esc_url(home_url('/')); ?>" title="xlstudy.in होम"><svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5M5.5 9.5V20h5v-6h3v6h5V9.5"/></svg></a><?php endif; ?><img src="<?php echo $eh_logo; ?>" alt="UPSSSC ALERT" width="44" height="44"><div><b>UPSSSC ALERT</b><span><?php echo esc_html($sub); ?></span></div></div>
  <div class="hdr"><?php echo $right; ?><?php if ($out && $eh_in): ?><a class="lo" href="<?php echo esc_url($eh_cfg['logout']); ?>" title="लॉग-आउट"><svg viewBox="0 0 24 24"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l-5-5 5-5M5 12h11"/></svg><span>लॉग-आउट</span></a><?php endif; ?><div class="xl"><i>XL</i><div><b>XL Study</b><span>xlstudy.in</span></div></div></div></header>
<?php };
$eh_ph = '<svg viewBox="0 0 40 40"><circle cx="20" cy="15" r="8"/><path d="M4 40c0-9 7-15 16-15s16 6 16 15z"/></svg>';
?><!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0f2a4d">
<title>UP Super TET 2026 — लाइव मॉक टेस्ट | UPSSSC ALERT × XL Study</title>
<link rel="icon" href="<?php echo $eh_logo; ?>">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700;800&family=Poppins:wght@500;600;700&display=swap">
<style>
:root{--nv:#0f2a4d;--nv2:#1c4a80;--pr:#1d63c7;--pr2:#e8f0fb;--gd:#c9a24a;--bg:#f2f5f9;--ln:#dbe3ec;--tx:#17202b;--mu:#5f6b7a;--gr:#2f9e55;--rd:#de4434;--pu:#6c43be;--or:#f29a0e}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0}
body{font-family:'Noto Sans Devanagari',system-ui,sans-serif;color:var(--tx);background:var(--bg);font-size:15px;line-height:1.5}
button{font-family:inherit;cursor:pointer}
.scr{display:none}.scr.on{display:block}
.num{font-family:Poppins,'Noto Sans Devanagari',sans-serif;font-variant-numeric:tabular-nums}

.hd{background:#fff;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 18px;box-shadow:0 1px 0 var(--ln),0 4px 14px rgba(15,42,77,.06);position:relative;z-index:6}
.brand{display:flex;align-items:center;gap:10px;min-width:0}
.brand img{width:44px;height:44px;border-radius:50%;flex:none;box-shadow:0 2px 6px rgba(0,0,0,.12)}
.brand b{display:block;font-family:Poppins,sans-serif;font-size:16px;color:var(--nv);letter-spacing:.3px;line-height:1.15}
.brand span{display:block;font-size:12px;color:var(--mu);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hdr{display:flex;align-items:center;gap:14px}
.xl{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border:1px solid var(--ln);border-radius:30px;background:#fbfcfe}
.xl i{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--nv),var(--pr));color:#fff;font-style:normal;font-weight:700;font-family:Poppins,sans-serif;font-size:13px;display:flex;align-items:center;justify-content:center}
.xl b{display:block;font-family:Poppins,sans-serif;font-size:13.5px;color:var(--nv);line-height:1.1}
.xl span{display:block;font-size:11px;color:var(--mu)}
.av{width:40px;height:40px;border-radius:50%;background:#e9eff6;border:2px solid #fff;box-shadow:0 0 0 1px var(--ln);display:flex;align-items:flex-end;justify-content:center;overflow:hidden;flex:none}
.av svg{width:34px;height:34px;fill:#a7b7c9}
.av.big{width:84px;height:84px}.av.big svg{width:72px;height:72px}
.cin{font-size:12.5px;line-height:1.45;color:var(--mu)}.cin b{color:var(--tx)}
.tmr{display:flex;align-items:center;gap:8px;background:var(--nv);color:#fff;border-radius:30px;padding:5px 14px 5px 6px}
.tmr i{width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-style:normal}
.tmr i svg{width:17px;height:17px;fill:none;stroke:#fff;stroke-width:2}
.tmr span{font-size:10.5px;opacity:.75;display:block;line-height:1}
.tmr b{font-family:Poppins,sans-serif;font-size:18px;letter-spacing:.5px;line-height:1.15}
.tmr.low{background:var(--rd);animation:bl 1s steps(2) infinite}
@keyframes bl{50%{background:#b8261a}}

.b{border:1px solid transparent;border-radius:10px;padding:11px 20px;font-size:14.5px;font-weight:600;line-height:1.2;transition:background .15s;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:6px}
.b:disabled{opacity:.5;cursor:not-allowed}
.b-pr{background:var(--pr);color:#fff;box-shadow:0 3px 10px rgba(29,99,199,.25)}.b-pr:hover:not(:disabled){background:#1654ad}
.b-gd{background:linear-gradient(135deg,#e1b955,#c9962e);color:#2b1d00;box-shadow:0 4px 14px rgba(201,150,46,.35)}
.b-wh{background:#fff;border-color:#c5d0dc;color:var(--tx)}.b-wh:hover:not(:disabled){background:#f4f7fb}
.b-lg{padding:14px 26px;font-size:16px;border-radius:12px}

/* landing */
.hero{position:relative;overflow:hidden;color:#fff;padding:38px 18px 100px;background:radial-gradient(520px 320px at 92% 8%,rgba(255,196,84,.38),transparent 62%),radial-gradient(560px 380px at 8% 110%,rgba(124,92,255,.42),transparent 62%),radial-gradient(400px 260px at 55% 120%,rgba(0,190,200,.28),transparent 65%),linear-gradient(125deg,#0a1b3f 0%,#123a86 48%,#1764c8 100%)}
.hero:before{content:"";position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.13) 1px,transparent 1.4px);background-size:22px 22px;-webkit-mask-image:linear-gradient(180deg,#000,transparent 85%);mask-image:linear-gradient(180deg,#000,transparent 85%)}
.hero:after{content:"";position:absolute;width:520px;height:520px;right:-170px;top:-200px;border-radius:50%;border:2px solid rgba(255,214,120,.25);box-shadow:0 0 0 46px rgba(255,214,120,.06),0 0 0 110px rgba(255,214,120,.04)}
.hin{position:relative;z-index:1}
.hero .ring .fgc{stroke:url(#rg)}
.ring{filter:drop-shadow(0 6px 16px rgba(0,0,0,.25))}
.ring .bgc{stroke:rgba(255,255,255,.18)}
.ring div{background:rgba(255,255,255,.06);border-radius:50%;margin:8px;backdrop-filter:blur(2px)}
.fact{position:relative;overflow:hidden;box-shadow:0 6px 18px -10px rgba(15,42,77,.25)}
.fact:before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:var(--fc,#1d63c7)}
.facts .fact:nth-child(1){--fc:linear-gradient(90deg,#1d63c7,#5b8def)}.facts .fact:nth-child(2){--fc:linear-gradient(90deg,#d39a22,#f3c561)}.facts .fact:nth-child(3){--fc:linear-gradient(90deg,#1f9d55,#55c98a)}.facts .fact:nth-child(4){--fc:linear-gradient(90deg,#de4434,#f08376)}
.hin{max-width:1080px;margin:0 auto;display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr);gap:28px;align-items:center}
.chip{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);padding:5px 14px;border-radius:30px;font-size:13px;font-weight:600}
.chip .dot{width:9px;height:9px;border-radius:50%;background:#ffcf5a}
.chip.live .dot{background:#3ddc78;animation:pl 1.6s infinite}
@keyframes pl{0%{box-shadow:0 0 0 0 rgba(61,220,120,.7)}70%{box-shadow:0 0 0 9px rgba(61,220,120,0)}100%{box-shadow:0 0 0 0 rgba(61,220,120,0)}}
.hero h1{font-family:Poppins,'Noto Sans Devanagari',sans-serif;font-size:clamp(30px,5vw,46px);line-height:1.08;margin:14px 0 6px}
.hero h1 em{font-style:normal;color:#ffd277}
.hero .st{font-size:16px;opacity:.9;margin:0 0 16px}
.when{display:flex;flex-wrap:wrap;gap:8px}
.when span{background:rgba(255,255,255,.1);border-radius:30px;padding:6px 14px;font-size:13.5px}
.cdw{text-align:center}
.cdw p{margin:0 0 12px;font-size:14px;opacity:.85}
.cd{display:flex;justify-content:center;gap:14px}
.ring{position:relative;width:92px;height:92px}
.ring svg{width:100%;height:100%;transform:rotate(-90deg)}
.ring circle{fill:none;stroke-width:6}
.ring .bgc{stroke:rgba(255,255,255,.14)}.ring .fgc{stroke:#ffd277;stroke-linecap:round}
.ring div{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.ring b{font-family:Poppins,sans-serif;font-size:28px;line-height:1}
.ring span{font-size:11.5px;opacity:.8;margin-top:3px}
.wrap{max-width:1080px;margin:0 auto;padding:0 14px}
.cta{background:#fff;border-radius:18px;box-shadow:0 10px 30px rgba(15,42,77,.13);margin-top:-64px;padding:20px 22px;display:flex;align-items:center;gap:18px;position:relative}
.cta .txt{flex:1;min-width:0}
.cta h3{margin:0 0 3px;font-size:18px;color:var(--nv)}
.cta p{margin:0;color:var(--mu);font-size:14px}
.cta .bt{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.facts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:18px 0}
.fact{background:#fff;border-radius:16px;padding:16px 12px;text-align:center;border:1px solid var(--ln)}
.fact i{width:52px;height:52px;margin:0 auto 8px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-style:normal;font-family:Poppins,sans-serif;font-weight:700;font-size:17px}
.fact b{display:block;font-size:14.5px}.fact span{font-size:12.5px;color:var(--mu)}
.i1{background:#e8f0fb;color:var(--pr)}.i2{background:#fdf3de;color:#a87612}.i3{background:#e6f6ec;color:var(--gr)}.i4{background:#fdeae7;color:var(--rd)}
.grid2{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;margin-bottom:16px}
.card{background:#fff;border-radius:16px;border:1px solid var(--ln);padding:18px 20px}
.card h2{margin:0 0 12px;font-size:17px;color:var(--nv);display:flex;align-items:center;gap:8px}
.card h2:before{content:"";width:8px;height:8px;border-radius:50%;background:var(--gd)}
.tl{list-style:none;margin:0;padding:0}
.tl li{display:flex;gap:14px;position:relative;padding-bottom:16px}
.tl li:last-child{padding-bottom:0}
.tl li:before{content:"";position:absolute;left:23px;top:48px;bottom:2px;width:2px;background:var(--ln)}
.tl li:last-child:before{display:none}
.tl .t{width:48px;height:48px;border-radius:50%;flex:none;background:var(--pr2);color:var(--pr);display:flex;align-items:center;justify-content:center;font-family:Poppins,sans-serif;font-size:11.5px;font-weight:600;text-align:center;line-height:1.15}
.tl li.on .t{background:var(--gr);color:#fff}
.tl b{display:block;font-size:14.5px;margin-top:3px}.tl span{font-size:13px;color:var(--mu)}
table.t{width:100%;border-collapse:separate;border-spacing:0;font-size:13.5px}
table.t th,table.t td{padding:8px 10px;border-bottom:1px solid #edf1f5;text-align:center}
table.t th{background:#f5f8fb;font-weight:600;color:#33414f}
table.t td:first-child,table.t th:first-child{text-align:left}
.tw{overflow-x:auto}
.rules{margin:0;padding-left:20px;line-height:1.8;font-size:14px}
.foot{text-align:center;color:var(--mu);font-size:12.5px;padding:18px 0 30px;display:flex;align-items:center;justify-content:center;gap:10px}
.foot img{width:26px;height:26px;border-radius:50%}

/* confirm + instructions */
.pg{max-width:1080px;margin:18px auto;padding:0 14px 120px}
.box{background:#fff;border-radius:16px;border:1px solid var(--ln);overflow:hidden}
.box>h3{margin:0;padding:12px 20px;background:var(--nv);color:#fff;font-size:15.5px;font-weight:600;display:flex;justify-content:space-between;align-items:center}
.box .in{padding:18px 22px}
.conf{display:flex;gap:22px;align-items:flex-start}
.conf dl{flex:1;margin:0;display:grid;grid-template-columns:150px minmax(0,1fr);gap:10px 14px;font-size:14.5px}
.conf dt{color:var(--mu)}.conf dd{margin:0;font-weight:600}
.ins{line-height:1.8;font-size:14.5px}
.ins ol{padding-left:22px;margin:6px 0 14px}.ins li{margin-bottom:6px}
.lg{display:flex;align-items:center;gap:10px;margin:7px 0}
.agree{display:flex;gap:12px;align-items:flex-start;background:#f6f9fc;border:1px solid var(--ln);border-radius:12px;padding:14px;margin-top:14px}
.agree input{width:22px;height:22px;flex:none;margin-top:2px;accent-color:var(--pr)}
.bar{position:fixed;left:0;right:0;bottom:0;background:#fff;box-shadow:0 -4px 16px rgba(15,42,77,.08);padding:10px 16px calc(10px + env(safe-area-inset-bottom));display:flex;justify-content:space-between;align-items:center;gap:10px;z-index:5}
.wait{font-size:13.5px;color:var(--mu);text-align:center;flex:1}
.wait b{color:var(--rd);font-family:Poppins,sans-serif}
.err{color:var(--rd);font-size:13px;min-height:18px;margin-top:6px}

/* palette shapes (TCS) */
.q{min-width:38px;height:34px;padding:0 4px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;font-family:Poppins,sans-serif;border:1px solid #a3afbc;background:#fff;color:#222;position:relative;border-radius:6px}
.q.na{background:var(--rd);border-color:var(--rd);color:#fff;border-radius:0 0 16px 16px}
.q.an{background:var(--gr);border-color:var(--gr);color:#fff;border-radius:16px 16px 0 0}
.q.mr,.q.am{background:var(--pu);border-color:var(--pu);color:#fff;border-radius:50%;min-width:36px;width:36px;height:36px}
.q.am:after{content:"";position:absolute;right:-3px;bottom:-2px;width:14px;height:14px;border-radius:50%;background:var(--gr) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M3 6.2l2 2 4-4.4' fill='none' stroke='%23fff' stroke-width='1.6'/%3E%3C/svg%3E") center/12px;border:1.5px solid #fff}

/* exam */
#s-exam{height:100vh;height:100dvh;flex-direction:column;overflow:hidden;background:#fff}
#s-exam.on{display:flex}
.secbar{display:flex;align-items:center;gap:8px;background:#f5f8fb;border-bottom:1px solid var(--ln);padding:7px 10px}
.secbar .lbl{font-size:12.5px;color:var(--mu);flex:none;padding:0 4px}
.secs{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;flex:1}
.secs::-webkit-scrollbar{display:none}
.secs button{flex:none;border:1px solid var(--ln);background:#fff;border-radius:30px;padding:6px 14px;font-size:13px;color:#24476f;white-space:nowrap}
.secs button.on{background:var(--nv2);border-color:var(--nv2);color:#fff}
.secs button small{display:inline-block;margin-left:6px;font-size:11px;background:#edf2f8;color:#24476f;border-radius:10px;padding:0 7px;font-family:Poppins,sans-serif}
.secs button.on small{background:rgba(255,255,255,.2);color:#fff}
.body{flex:1;display:flex;min-height:0}
.main{flex:1;display:flex;flex-direction:column;min-width:0}
.qbar{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:8px 16px;border-bottom:1px solid #edf1f5;flex-wrap:wrap}
.qbar .no{font-weight:700;font-size:15.5px;color:var(--nv)}
.qbar .r{display:flex;gap:10px;align-items:center;font-size:12.5px;color:var(--mu)}
.mk{display:inline-flex;gap:4px}.mk i{font-style:normal;font-weight:700;border-radius:20px;padding:1px 8px;font-family:Poppins,sans-serif}
.mk .p{background:#e6f6ec;color:var(--gr)}.mk .n{background:#fdeae7;color:var(--rd)}
.qbar select{font-size:12.5px;padding:4px 8px;border:1px solid var(--ln);border-radius:8px;font-family:inherit;background:#fff}
.sv{font-size:11.5px;display:inline-flex;align-items:center;gap:5px}
.sv:before{content:"";width:8px;height:8px;border-radius:50%;background:var(--gr)}
.sv.w:before{background:var(--or)}.sv.x:before{background:var(--rd)}.sv.x{color:var(--rd)}
.qa{flex:1;overflow:auto;padding:16px 20px 24px;-webkit-overflow-scrolling:touch}
.pass{border:1px solid var(--ln);background:#f8fafc;border-radius:12px;padding:12px 14px;margin-bottom:14px;line-height:1.8;font-size:14.5px;max-height:40vh;overflow:auto}
.pass b{display:block;font-size:12.5px;color:var(--mu);margin-bottom:4px}
.qt{font-size:16.5px;line-height:1.85;margin-bottom:14px}
.op{display:flex;gap:12px;align-items:flex-start;padding:11px 14px;margin-bottom:9px;line-height:1.65;cursor:pointer;font-size:15.5px;border:1px solid #e1e8f0;border-radius:12px;background:#fff;position:relative}
.op:hover{border-color:#b9cde6}
.op.sel{background:#eaf2fd;border-color:var(--pr);box-shadow:inset 0 0 0 1px var(--pr)}
.op input{position:absolute;opacity:0;pointer-events:none}
.op .k{width:28px;height:28px;border-radius:50%;border:1.5px solid #a9b6c4;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;font-family:Poppins,sans-serif;flex:none;color:#506173;margin-top:1px}
.op.sel .k{background:var(--pr);border-color:var(--pr);color:#fff}
.acts{display:flex;justify-content:space-between;gap:8px;padding:10px 16px;border-top:1px solid var(--ln);background:#fff}
.acts .l,.acts .r{display:flex;gap:8px}
.acts .b{padding:10px 16px;font-size:14px}
.b-mk{background:#fff;border-color:var(--pu);color:var(--pu)}
.side{width:310px;flex:none;border-left:1px solid var(--ln);background:#f3f7fc;display:flex;flex-direction:column;min-height:0}
.side .cand{display:flex;gap:10px;align-items:center;padding:12px 14px;background:#fff;border-bottom:1px solid var(--ln)}
.side .cand b{display:block;font-size:14px}.side .cand span{font-size:12px;color:var(--mu)}
.lgd{display:grid;grid-template-columns:1fr 1fr;gap:9px;padding:12px 14px;font-size:12px;background:#fff;border-bottom:1px solid var(--ln)}
.lgd div{display:flex;align-items:center;gap:7px;line-height:1.25}
.lgd .w{grid-column:1/3}
.lgd .q{min-width:32px;height:30px;font-size:12px}.lgd .q.mr,.lgd .q.am{min-width:32px;width:32px;height:32px}
.pview{margin:12px 14px 0;background:var(--nv2);color:#fff;padding:8px 12px;font-size:13px;border-radius:10px}
.palg{flex:1;overflow:auto;padding:14px;display:grid;grid-template-columns:repeat(5,1fr);gap:11px 8px;align-content:start;justify-items:center}
.palg .q.cur{box-shadow:0 0 0 2px #fff,0 0 0 4px var(--or)}
.side .ft{padding:12px 14px;background:#fff;border-top:1px solid var(--ln);display:grid;grid-template-columns:1fr 1fr;gap:8px}
.side .ft .b{padding:10px 8px;font-size:13.5px}
.side .ft .b-pr{grid-column:1/3;padding:12px;font-size:15px}
.mob{display:none}
.grab{display:none;justify-content:space-between;align-items:center;padding:12px 14px;background:#fff;border-bottom:1px solid var(--ln);font-weight:700}
.grab:before{content:"";position:absolute;top:6px;left:50%;width:40px;height:4px;margin-left:-20px;border-radius:4px;background:#d2dbe5}
.grab button{border:0;background:#eef3f8;border-radius:50%;width:34px;height:34px;font-size:20px;line-height:1}
.pbtn{align-items:center;gap:6px;border:1px solid var(--ln);background:#fff;border-radius:30px;padding:6px 12px;font-size:12.5px;color:var(--nv2);font-weight:600}
.pbtn svg{width:15px;height:15px;fill:var(--nv2)}
.shade{display:none;position:fixed;inset:0;background:rgba(10,25,45,.45);z-index:20}.shade.on{display:block}

/* modal */
.mdl{position:fixed;inset:0;background:rgba(10,25,45,.5);display:none;align-items:center;justify-content:center;z-index:40;padding:12px}
.mdl.on{display:flex}
.mdl .bx{background:#fff;width:100%;max-width:780px;max-height:92vh;display:flex;flex-direction:column;border-radius:16px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.25)}
.mdl .bx>h3{margin:0;background:var(--nv);color:#fff;padding:12px 18px;font-size:15.5px;font-weight:600;display:flex;justify-content:space-between;align-items:center}
.mdl .bx>h3 button{background:rgba(255,255,255,.12);border:0;color:#fff;font-size:20px;line-height:1;width:32px;height:32px;border-radius:50%}
.mdl .in{padding:16px 18px;overflow:auto}
.mdl .ftr{padding:12px 18px;border-top:1px solid var(--ln);display:flex;justify-content:flex-end;gap:8px}
.mdl .ftr:empty{display:none}

/* result */
.rhero{background:linear-gradient(135deg,#0d2443,#1c4a80);color:#fff;padding:24px 16px 80px}
.rhero .in{max-width:1080px;margin:0 auto;display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.rhero h2{margin:0;font-size:22px;font-family:Poppins,'Noto Sans Devanagari',sans-serif}
.rhero p{margin:2px 0 0;opacity:.85;font-size:13.5px}
.rtop{background:#fff;border-radius:18px;box-shadow:0 10px 30px rgba(15,42,77,.12);margin-top:-60px;padding:20px;display:grid;grid-template-columns:auto minmax(0,1fr);gap:24px;align-items:center}
.gauge{position:relative;width:170px;height:170px}
.gauge svg{width:170px;height:170px;transform:rotate(-90deg)}
.gauge circle{fill:none;stroke-width:13}
.gauge .bgc{stroke:#edf2f8}.gauge .fgc{stroke:url(#gg);stroke-linecap:round;transition:stroke-dashoffset 1.2s ease}
.gauge div{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.gauge b{font-family:Poppins,sans-serif;font-size:40px;color:var(--nv);line-height:1}
.gauge span{font-size:12.5px;color:var(--mu);margin-top:4px}
.kps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.kp{border:1px solid #e8edf3;border-radius:14px;padding:12px;display:flex;align-items:center;gap:12px}
.kp i{width:44px;height:44px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;font-style:normal;font-family:Poppins,sans-serif;font-weight:700;font-size:15px}
.kp b{display:block;font-family:Poppins,sans-serif;font-size:19px;line-height:1.15}.kp span{font-size:12px;color:var(--mu)}
.note{background:#fff8e6;border:1px solid #f1dc9f;border-radius:12px;padding:10px 14px;font-size:13.5px;margin:14px 0;line-height:1.6}
.cmp .row{display:grid;grid-template-columns:90px minmax(0,1fr) 60px;gap:10px;align-items:center;margin:10px 0;font-size:13.5px}
.track{height:12px;background:#edf2f8;border-radius:10px;overflow:hidden;display:flex}
.track i{display:block;height:100%}
.secr{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,2fr) 64px;gap:10px;align-items:center;padding:9px 0;border-bottom:1px solid #f0f3f7;font-size:13.5px}
.secr small{display:block;color:var(--mu);font-size:12px}
.secr .m{text-align:right;font-family:Poppins,sans-serif;font-weight:600}
.lgnd{display:flex;gap:14px;font-size:12px;color:var(--mu);margin-bottom:6px;flex-wrap:wrap}
.lgnd span:before{content:"";display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:5px;vertical-align:-1px;background:var(--c)}
.lb{list-style:none;margin:0;padding:0}
.lb li{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid #f0f3f7;font-size:14px}
.lb li i{width:28px;height:28px;border-radius:50%;background:#eef3f8;display:flex;align-items:center;justify-content:center;font-style:normal;font-family:Poppins,sans-serif;font-size:12.5px;font-weight:700}
.lb li:nth-child(1) i{background:#ffe7a6}.lb li:nth-child(2) i{background:#e5e9ef}.lb li:nth-child(3) i{background:#f6d6bd}
.lb li b{margin-left:auto;font-family:Poppins,sans-serif}
.filt{display:flex;gap:8px;flex-wrap:wrap;margin:6px 0 12px}
.filt button{border:1px solid var(--ln);background:#fff;padding:7px 14px;border-radius:30px;font-size:13px}
.filt button.on{background:var(--nv);color:#fff;border-color:var(--nv)}
.sol{background:#fff;border:1px solid var(--ln);border-radius:14px;padding:14px 16px;margin-bottom:10px;line-height:1.75}
.sol .h{display:flex;justify-content:space-between;gap:8px;font-size:12.5px;color:var(--mu);margin-bottom:4px}
.sol .o{padding:4px 10px;border-radius:8px;margin:3px 0}
.sol .ok{background:#e3f5e6;color:#14532d;font-weight:600}.sol .bad{background:#fde6e2;color:#8a1c0c}
.sol .ex{background:#f4f8fc;border-left:3px solid var(--pr);border-radius:0 8px 8px 0;padding:8px 12px;margin-top:8px;font-size:14px}
.tg{font-size:12px;font-weight:700;padding:2px 10px;border-radius:20px;white-space:nowrap}
.tg.c{background:#dcfce7;color:#166534}.tg.w{background:#fee2e2;color:#991b1b}.tg.s{background:#eef2f7;color:#475569}

/* admin */
.adm{max-width:1200px;margin:16px auto;padding:0 14px 40px}
.ak{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:14px}
.ak div{background:#fff;border:1px solid var(--ln);border-radius:14px;padding:14px;text-align:center}
.ak b{display:block;font-family:Poppins,sans-serif;font-size:24px;color:var(--nv)}.ak span{font-size:12.5px;color:var(--mu)}
.pill{display:inline-block;font-size:12px;font-weight:600;padding:2px 10px;border-radius:20px;white-space:nowrap}
.p-l{background:#dcfce7;color:#166534}.p-s{background:#e8f0fb;color:#1d4f9a}.p-o{background:#eef2f7;color:#5b6675}.p-i{background:#fff4d6;color:#8a5a00}

/* end test button */
.ibar .ir{display:flex;align-items:center;gap:10px}
.endb{display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 14px;border-radius:30px;border:1.5px solid rgba(255,255,255,.55);background:rgba(222,68,52,.92);color:#fff;font-weight:700;font-size:13px}
.endb svg{width:13px;height:13px;fill:#fff}
.endb:hover{background:#c5301f}
@media(max-width:760px){.endb{height:34px;padding:0 11px;font-size:12px}.ibar .ir{gap:7px}}
.mtools{display:none}
@media(max-width:760px){
 .mtools{display:flex;gap:8px;padding:6px 12px;border-bottom:1px solid #edf1f5;background:#fafcfe}
 .mtools .b{flex:1;padding:8px 6px;font-size:13px;border-radius:30px}
 #bback,#bclr{display:none!important}
 .acts{grid-template-columns:1fr 1fr!important}
}
/* home button */
.hm{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;width:40px;padding:0;margin-right:4px;border-radius:30px;text-decoration:none;color:#fff;font-weight:700;font-size:13.5px;background:linear-gradient(135deg,#1d63c7,#0f2a4d);box-shadow:0 4px 12px -4px rgba(29,99,199,.6);transition:transform .15s,box-shadow .15s;flex:none}
.hm:hover{transform:translateY(-1px);box-shadow:0 8px 18px -6px rgba(29,99,199,.7)}
.brand .hm span{display:inline;color:#fff;font-size:13.5px;white-space:nowrap}
.hm svg{width:19px;height:19px;fill:none;stroke:#fff;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
@media(max-width:760px){.hm{height:36px;width:36px;padding:0;justify-content:center;margin-right:2px}.hm span{display:none}.hm svg{width:18px;height:18px}}
/* logout, info bar, watermark, smooth */
.lo{display:inline-flex;align-items:center;gap:6px;color:var(--mu);text-decoration:none;font-size:13px;font-weight:600;border:1px solid var(--ln);border-radius:30px;padding:6px 12px;background:#fff}
.lo svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.lo:hover{color:var(--rd);border-color:#f1c4bd}
.hd{padding-top:max(8px,env(safe-area-inset-top))}
.ibar{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 16px;background:linear-gradient(90deg,#0f2a4d,#1c4a80);color:#fff}
.ibar .who{display:flex;align-items:center;gap:10px;min-width:0}
.ibar .who .av{width:36px;height:36px;border-color:rgba(255,255,255,.6);box-shadow:none}
.ibar .who b{display:block;font-size:14px;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:46vw}
.ibar .who span{font-size:11.5px;opacity:.8}
.ibar .tmr{background:#fff;color:var(--nv);padding:4px 14px 4px 5px}
.ibar .tmr i{background:var(--pr2)}.ibar .tmr i svg{stroke:var(--nv)}
.ibar .tmr span{opacity:.65}
.ibar .tmr.low{background:#fff;color:var(--rd);animation:bl2 1s steps(2) infinite}
@keyframes bl2{50%{background:#ffe3df}}
#s-exam{position:fixed;top:0;left:0;right:0;bottom:0;height:auto!important;z-index:10}
#s-exam .main{min-height:0}
#s-exam .acts{flex:none;position:relative;z-index:3;box-shadow:0 -4px 14px rgba(15,42,77,.08)}
.qa{background-color:#fff;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='460' height='300' viewBox='0 0 460 300'%3E%3Ctext x='230' y='160' text-anchor='middle' transform='rotate(-24 230 150)' font-family='Arial,Helvetica,sans-serif' font-size='28' font-weight='800' letter-spacing='3' fill='%230f2a4d' fill-opacity='0.055'%3EUPSSSC ALERT%3C/text%3E%3C/svg%3E");background-attachment:local}
.scr.on{animation:fi .25s ease}
@keyframes fi{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
#s-exam.on{animation:none}
.op{transition:background .12s,border-color .12s}
.op:active{transform:scale(.995)}
/* result extras */
.hstat{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px}
.hstat span{background:rgba(255,255,255,.12);border-radius:30px;padding:5px 14px;font-size:13.5px}
.hstat b{font-family:Poppins,sans-serif;color:#ffd277}
.livebadge{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#166534;background:#dcfce7;border-radius:20px;padding:2px 10px;margin-left:auto}
.livebadge:before{content:"";width:8px;height:8px;border-radius:50%;background:#22a35a;animation:pl 1.6s infinite}
.dist{overflow-x:auto;padding:8px 2px 4px;scrollbar-width:thin}
.dist .bars{display:flex;align-items:flex-end;gap:6px;height:190px;min-width:max-content;border-bottom:2px solid #e3e9f0;padding:0 4px}
.dist .bc{width:44px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%}
.dist .bc b{font-size:12px;font-family:Poppins,sans-serif;color:var(--nv);margin-bottom:3px}
.dist .bc i{display:block;width:100%;border-radius:8px 8px 0 0;background:linear-gradient(180deg,#7fa8e0,#1d63c7);min-height:3px;transition:height .6s ease}
.dist .bc.me i{background:linear-gradient(180deg,#ffd277,#c9962e)}
.dist .lb2{display:flex;gap:6px;min-width:max-content;padding:4px 4px 0}
.dist .lb2 span{width:44px;text-align:center;font-size:11px;color:var(--mu);font-family:Poppins,sans-serif}
.dist .lb2 span.me{color:#a87612;font-weight:700}
.atts{display:flex;gap:10px;overflow-x:auto;padding-bottom:4px}.atts div{flex:none;width:86px;height:86px;border-radius:50%;border:2px solid var(--ln);display:flex;flex-direction:column;align-items:center;justify-content:center}.atts div.on{border-color:var(--pr);background:var(--pr2)}.atts b{font-family:Poppins,sans-serif;font-size:20px;color:var(--nv)}.atts span{font-size:11.5px;color:var(--mu)}
.solbtn{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--ln);border-radius:16px;padding:16px 18px;margin:16px 0;cursor:pointer;width:100%;text-align:left;font:inherit;color:inherit}
.solbtn i{width:48px;height:48px;border-radius:50%;background:var(--pr2);color:var(--pr);display:flex;align-items:center;justify-content:center;font-style:normal;font-size:22px;flex:none}
.solbtn b{display:block;font-size:16px;color:var(--nv)}.solbtn span{font-size:13px;color:var(--mu)}
.solbtn em{margin-left:auto;font-style:normal;font-size:22px;color:var(--pr)}
.join{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0}
.join a{display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:16px;color:#fff;text-decoration:none;background:linear-gradient(135deg,#1f8fd6,#2aa3e8);box-shadow:0 6px 18px rgba(31,143,214,.25)}
.join a.g{background:linear-gradient(135deg,#0f2a4d,#1c4a80);box-shadow:0 6px 18px rgba(15,42,77,.25)}
.join a i{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex:none}
.join a i svg{width:22px;height:22px;fill:#fff}
.join a b{display:block;font-size:15px}.join a span{font-size:12.5px;opacity:.85}
.promo{border-radius:22px;overflow:hidden;background:radial-gradient(700px 300px at 100% 0,rgba(255,210,119,.25),transparent 60%),linear-gradient(135deg,#0d2443,#1c4a80);color:#fff;display:grid;grid-template-columns:minmax(0,1.3fr) minmax(0,1fr);margin:18px 0 10px;box-shadow:0 14px 34px rgba(15,42,77,.22)}
.promo .pt{padding:24px 26px}
.promo .tagp{display:inline-block;background:#ffd277;color:#3b2a00;font-weight:800;font-size:12px;border-radius:20px;padding:3px 12px}
.promo h3{font-family:Poppins,'Noto Sans Devanagari',sans-serif;font-size:26px;margin:10px 0 4px;line-height:1.15}
.promo h3 em{font-style:normal;color:#ffd277}
.promo p{margin:0 0 12px;opacity:.88;font-size:14px}
.promo ul{list-style:none;margin:0 0 16px;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;font-size:14px}
.promo li{display:flex;gap:8px;align-items:flex-start}
.promo li:before{content:"✓";width:20px;height:20px;border-radius:50%;background:rgba(61,220,120,.2);color:#6ff0a0;font-size:12px;display:flex;align-items:center;justify-content:center;flex:none;margin-top:2px}
.promo .pr{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.promo .fee{font-family:Poppins,sans-serif;font-size:30px;font-weight:700;color:#ffd277;line-height:1}
.promo .fee small{display:block;font-size:11.5px;color:#fff;opacity:.75;font-weight:500;font-family:'Noto Sans Devanagari',sans-serif}
.promo .pb{display:flex;flex-direction:column;justify-content:center;align-items:center;gap:12px;padding:24px;background:rgba(255,255,255,.06);border-left:1px solid rgba(255,255,255,.1);text-align:center}
.promo .pb img{width:150px;border-radius:10px;box-shadow:0 10px 24px rgba(0,0,0,.35);transform:rotate(-3deg)}
.promo .pb b{font-size:15px}.promo .pb span{font-size:13px;opacity:.85}
.b-wa{background:#25d366;color:#fff}
#s-sol .top{position:sticky;top:0;z-index:5;background:#fff;box-shadow:0 2px 10px rgba(15,42,77,.08);padding:10px 14px;display:flex;align-items:center;gap:10px}
#s-sol .top h3{margin:0;font-size:16px;color:var(--nv)}
.pdfwait{position:fixed;inset:0;background:rgba(10,25,45,.6);z-index:60;display:none;align-items:center;justify-content:center;color:#fff;flex-direction:column;gap:14px;font-size:15px}
.pdfwait.on{display:flex}
.pdfwait .pc{width:90px;height:90px;border-radius:50%;border:6px solid rgba(255,255,255,.2);border-top-color:#ffd277;animation:sp 1s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
#pdfStage{position:fixed;left:-10000px;top:0;width:760px;background:#fff;font-family:'Noto Sans Devanagari',Arial,sans-serif;color:#17202b}
@media(max-width:760px){
 .lo span{display:none}.lo{padding:7px}
 .ibar{padding:6px 10px}.ibar .who .av{display:none}.ibar .tmr b{font-size:17px}
 .acts .l{display:contents}.acts .r{display:contents}
 #bmark{order:1}#bsave{order:2}#bback{order:3}#bclr{order:4}
 .promo{grid-template-columns:1fr}.promo .pb{border-left:0;border-top:1px solid rgba(255,255,255,.1)}.promo ul{grid-template-columns:1fr}.promo h3{font-size:22px}
 .join{grid-template-columns:1fr}
}
#printArea{display:none}
@media print{.scr,.mdl,.shade{display:none!important}#printArea{display:block;font-size:11.5px;line-height:1.5}
 #printArea h1{text-align:center;font-size:16px;margin:0 0 2px}#printArea .pq{break-inside:avoid;margin-bottom:7px}
 #printArea .cols{column-count:2;column-gap:20px}#printArea .key{display:grid;grid-template-columns:repeat(10,1fr);gap:3px}}

@media(max-width:1024px){.side{width:270px}.palg{grid-template-columns:repeat(4,1fr)}.hin{grid-template-columns:1fr}.cdw{text-align:left}.cd{justify-content:flex-start}}
@media(max-width:760px){
 .hd{padding:7px 10px}.brand img{width:38px;height:38px}.brand b{font-size:14px}.brand span{font-size:11px}
 .xl{padding:3px 9px 3px 3px}.xl i{width:28px;height:28px;font-size:11.5px}.xl b{font-size:12px}.xl span{display:none}
 .hdr{gap:8px}.hdr .cin,.hdr .av{display:none}
 .tmr{padding:4px 12px 4px 4px}.tmr i{width:26px;height:26px}.tmr b{font-size:16px}
 .hero{padding:24px 16px 84px}.hero .st{font-size:14.5px}.ring{width:72px;height:72px}.ring b{font-size:22px}.ring span{font-size:10.5px}.cd{gap:10px}
 .cta{flex-direction:column;align-items:stretch;text-align:center;gap:12px;padding:18px}.cta .av{margin:0 auto}.cta .bt{justify-content:stretch}.cta .bt .b{flex:1}
 .facts{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
 .grid2{grid-template-columns:1fr}
 .conf{flex-direction:column;align-items:center}.conf dl{grid-template-columns:118px minmax(0,1fr);width:100%;font-size:14px}
 .box .in{padding:16px}.box>h3{padding:11px 16px;font-size:14.5px}
 .bar .b{flex:1;padding:12px 10px}.bar{flex-wrap:wrap}.wait{order:-1;flex-basis:100%}.wait:empty{display:none}
 .secbar .lbl{display:none}
 .qbar{padding:8px 12px}.qbar .lang{font-size:0}.qbar .lang select{font-size:12.5px;padding:5px 6px}.qbar .sv{display:none}
 .qa{padding:12px 12px 16px}.qt{font-size:16px}.op{font-size:15.5px;padding:11px 12px}
 .acts{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:8px 10px calc(8px + env(safe-area-inset-bottom))}
 .acts .l,.acts .r{display:contents}.acts .b{padding:12px 6px;font-size:13.5px;width:100%}
 .mob{display:inline-flex}
 .side{position:fixed;left:0;right:0;bottom:0;top:auto;width:auto;height:84vh;height:84dvh;border:0;border-radius:20px 20px 0 0;z-index:30;transform:translateY(105%);transition:transform .25s ease;box-shadow:0 -10px 30px rgba(0,0,0,.25);overflow:hidden}
 .side.open{transform:none}.grab{display:flex;position:relative;padding-top:18px}
 .palg{grid-template-columns:repeat(6,1fr)}
 .rtop{grid-template-columns:1fr;justify-items:center}.kps{grid-template-columns:repeat(2,minmax(0,1fr));width:100%}
 .secr{grid-template-columns:minmax(0,1fr) 60px}.secr .track{grid-column:1/3;grid-row:2}
 table.t{font-size:12.5px}table.t th,table.t td{padding:7px 5px}
 .ak{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<svg width="0" height="0" style="position:absolute"><defs><linearGradient id="gg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1d63c7"/><stop offset="1" stop-color="#2f9e55"/></linearGradient><linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffe29a"/><stop offset="1" stop-color="#ff9f43"/></linearGradient></defs></svg>

<!-- ===== home ===== -->
<section class="scr on" id="s-home">
 <?php $eh_hd('Online Examination', '', true); ?>
 <div class="hero"><div class="hin">
  <div><span class="chip" id="h-chip"><span class="dot"></span><span id="h-chipt">लाइव मॉक टेस्ट</span></span>
   <h1>UP Super TET <em>2026</em></h1>
   <p class="st">सहायक अध्यापक भर्ती परीक्षा · प्राथमिक स्तर (कक्षा 1–5) · ऑनलाइन लाइव मॉक</p>
   <div class="when"><span>📅 <b id="h-date"></b></span><span>⏰ <b id="h-time"></b></span></div></div>
  <div class="cdw"><p id="cd-lbl">परीक्षा शुरू होने में</p><div class="cd" id="cd"></div></div>
 </div></div>
 <div class="wrap">
  <div class="cta"><div class="av big"><?php echo $eh_ph; ?></div><div class="txt"><h3 id="c-h"></h3><p id="c-p"></p></div><div class="bt" id="c-bt"></div></div>
  <div class="facts">
   <div class="fact"><i class="i1">120</i><b>प्रश्न</b><span>बहुविकल्पीय (MCQ)</span></div>
   <div class="fact"><i class="i2">360</i><b>पूर्णांक</b><span>प्रत्येक प्रश्न 3 अंक</span></div>
   <div class="fact"><i class="i3">120</i><b>मिनट</b><span>सर्वर पर चलने वाली घड़ी</span></div>
   <div class="fact"><i class="i4">−1</i><b>नकारात्मक अंक</b><span>प्रत्येक गलत उत्तर पर</span></div>
  </div>
  <div class="grid2">
   <div class="card"><h2>परीक्षा का समय-क्रम</h2><ul class="tl" id="tl"></ul></div>
   <div class="card"><h2>प्रश्न-पत्र का स्वरूप</h2><div class="tw"><table class="t"><thead><tr><th>विषय</th><th>प्रश्न</th><th>अंक</th></tr></thead><tbody id="pat"></tbody></table></div></div>
  </div>
  <div class="card" style="margin-bottom:16px"><h2>महत्वपूर्ण निर्देश</h2><ol class="rules">
   <li>परीक्षा देने के लिए xlstudy.in पर लॉग-इन ज़रूरी है। जितनी बार चाहें दोबारा दे सकते हैं — रैंक में आपका सबसे नया प्रयास गिना जाएगा।</li>
   <li>मॉक सुबह 10 बजे से रात 11:59 तक खुला है — इस बीच कभी भी शुरू करें, शुरू करने के बाद पूरे 120 मिनट मिलेंगे। अंतिम रैंक रात 11:59 के बाद।</li>
   <li>हर उत्तर सर्वर पर सेव होता है। नेट जाने या फ़ोन बंद होने पर दोबारा खोलें — परीक्षा वहीं से चलेगी।</li>
   <li>समय पूरा होते ही परीक्षा अपने आप सबमिट हो जाएगी।</li>
   <li>सबमिट के बाद स्कोर, रैंक, परसेंटाइल, सेक्शन-वार विश्लेषण और हर प्रश्न की व्याख्या मिलेगी। प्रश्न-पत्र व उत्तर-कुंजी PDF में डाउनलोड कर सकेंगे।</li>
   <li>परीक्षा के दौरान दूसरी टैब/ऐप खोलना वर्जित है, इसकी गिनती दर्ज होती है।</li>
  </ol></div>
  <div class="foot"><img src="<?php echo $eh_logo; ?>" alt=""> UPSSSC ALERT × XL Study · xlstudy.in</div>
 </div>
</section>

<!-- ===== candidate confirm ===== -->
<section class="scr" id="s-conf">
 <?php $eh_hd('Candidate Details'); ?>
 <div class="pg"><div class="box"><h3><span>उम्मीदवार का विवरण</span><span>Confirm</span></h3><div class="in"><div class="conf">
  <div class="av big"><?php echo $eh_ph; ?></div>
  <dl><dt>उम्मीदवार का नाम</dt><dd class="cn"></dd><dt>रोल नंबर</dt><dd class="roll num"></dd><dt>परीक्षा</dt><dd>UP Super TET 2026 — लाइव मॉक</dd><dt>पद</dt><dd>सहायक अध्यापक (प्राथमिक स्तर)</dd><dt>परीक्षा तिथि</dt><dd id="cf-date"></dd><dt>परीक्षा केंद्र</dt><dd>ऑनलाइन · xlstudy.in</dd></dl>
 </div></div></div></div>
 <div class="bar"><button class="b b-wh" data-go="s-home">‹ वापस</button><button class="b b-pr" data-go="s-ins1">विवरण सही है — आगे ›</button></div>
</section>

<!-- ===== instructions 1 ===== -->
<section class="scr" id="s-ins1">
 <?php $eh_hd('General Instructions', '<div class="cin">Candidate: <b class="cn"></b></div>'); ?>
 <div class="pg"><div class="box"><h3><span>सामान्य निर्देश</span><span>1 / 2</span></h3><div class="in ins">
  <b>कृपया निम्नलिखित निर्देशों को ध्यानपूर्वक पढ़ें:</b><ol>
  <li>परीक्षा की कुल अवधि <b>120 मिनट</b> है। सर्वर पर लगी घड़ी ऊपर दाईं ओर बचा हुआ समय दिखाएगी। समय समाप्त होने पर परीक्षा अपने आप सबमिट हो जाएगी।</li>
  <li>प्रश्न-पत्र में <b>120 प्रश्न</b> हैं। प्रत्येक सही उत्तर पर <b>3 अंक</b> मिलेंगे और प्रत्येक गलत उत्तर पर <b>1 अंक</b> काटा जाएगा। छोड़े गए प्रश्न पर कोई अंक नहीं।</li>
  <li>प्रश्न-पैलेट प्रत्येक प्रश्न की स्थिति इन चिह्नों से दिखाएगा:
   <div class="lg"><span class="q">1</span> आपने यह प्रश्न अभी तक नहीं देखा है।</div>
   <div class="lg"><span class="q na">2</span> आपने इस प्रश्न का उत्तर नहीं दिया है।</div>
   <div class="lg"><span class="q an">3</span> आपने इस प्रश्न का उत्तर दे दिया है।</div>
   <div class="lg"><span class="q mr">4</span> उत्तर नहीं दिया, पर पुनर्विचार हेतु चिह्नित किया है।</div>
   <div class="lg"><span class="q am">5</span> उत्तर दिया और पुनर्विचार हेतु चिह्नित किया — इसका मूल्यांकन किया जाएगा।</div></li>
  <li>मोबाइल पर प्रश्न-पैलेट <b>Palette</b> बटन से नीचे से खुलेगा।</li></ol>
  <b>प्रश्न पर जाना और उत्तर देना:</b><ol start="5">
  <li>पैलेट में प्रश्न संख्या दबाकर सीधे उस प्रश्न पर जाएँ। ऐसा करने से वर्तमान प्रश्न का चुना हुआ उत्तर सेव नहीं होगा।</li>
  <li><b>Save &amp; Next</b> — उत्तर सेव होगा और अगला प्रश्न खुलेगा।</li>
  <li><b>Mark for Review &amp; Next</b> — उत्तर (यदि चुना है) सेव होगा, प्रश्न चिह्नित होगा और अगला प्रश्न खुलेगा।</li>
  <li><b>Clear Response</b> — चुना हुआ उत्तर हट जाएगा।</li></ol>
 </div></div></div>
 <div class="bar"><button class="b b-wh" data-go="s-conf">‹ Previous</button><button class="b b-pr" data-go="s-ins2">Next ›</button></div>
</section>

<!-- ===== instructions 2 ===== -->
<section class="scr" id="s-ins2">
 <?php $eh_hd('Other Important Instructions', '<div class="cin">Candidate: <b class="cn"></b></div>'); ?>
 <div class="pg"><div class="box"><h3><span>अन्य महत्वपूर्ण निर्देश</span><span>2 / 2</span></h3><div class="in ins">
  <div class="tw"><table class="t" style="max-width:640px"><thead><tr><th>सेक्शन</th><th>प्रश्न</th><th>अंक</th></tr></thead><tbody id="pat2"></tbody></table></div>
  <ol style="margin-top:14px"><li>प्रश्न-पत्र में 10 सेक्शन हैं। ऊपर सेक्शन के नाम पर क्लिक करके किसी भी सेक्शन में कभी भी जा सकते हैं।</li>
  <li>परीक्षा के दौरान दूसरी टैब/ऐप खोलना वर्जित है। ऐसा करने पर चेतावनी आएगी और गिनती दर्ज होगी।</li>
  <li>प्रश्न-पत्र हिंदी और English दोनों भाषाओं में है। परीक्षा के दौरान ऊपर <b>View in / भाषा</b> से कभी भी भाषा बदल सकते हैं। हिंदी, संस्कृत और अंग्रेज़ी भाषा के प्रश्न अपनी ही भाषा में रहेंगे।</li>
  <li>परीक्षा शुरू होने का समय होते ही नीचे का बटन अपने आप चालू हो जाएगा।</li></ol>
  <label style="display:block">डिफ़ॉल्ट भाषा / Default Language: <select id="lsel0" style="padding:7px 10px;font-size:14px;border-radius:8px;border:1px solid var(--ln)"><option value="hi">हिंदी</option><option value="en">English</option></select></label>
  <label class="agree"><input type="checkbox" id="agree"><span>मैंने सभी निर्देश पढ़ और समझ लिए हैं। मैं घोषणा करता/करती हूँ कि परीक्षा में किसी भी अनुचित साधन का प्रयोग नहीं करूँगा/करूँगी।</span></label>
  <div class="err" id="ag-err"></div>
 </div></div></div>
 <div class="bar"><button class="b b-wh" data-go="s-ins1">‹ Previous</button><span class="wait" id="wait"></span><button class="b b-pr" id="begin">I am ready to begin</button></div>
</section>

<!-- ===== exam ===== -->
<section class="scr" id="s-exam">
 <?php $eh_hd('UP Super TET 2026 · Live Mock', '', false, false); ?>
 <div class="ibar"><div class="who"><div class="av"><?php echo $eh_ph; ?></div><div><b class="cn"></b><span>Roll No: <span class="roll"></span></span></div></div>
  <div class="ir"><button class="endb" id="bend" type="button"><svg viewBox="0 0 24 24"><path d="M6 6h12v12H6z"/></svg><span>End Test</span></button><div class="tmr" id="tbox"><i><svg viewBox="0 0 24 24"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/></svg></i><div><span>Time Left</span><b id="tm">2:00:00</b></div></div></div></div>
 <div class="secbar"><span class="lbl">Sections</span><div class="secs" id="secs"></div></div>
 <div class="body">
  <div class="main">
   <div class="qbar"><span class="no" id="qno">Question No. 1</span>
    <span class="r"><span class="sv" id="sv">Saved</span><span class="lang">View in <select id="lsel"><option value="hi">हिंदी</option><option value="en">English</option></select></span><span class="mk"><i class="p">+3</i><i class="n">−1</i></span>
    <button class="pbtn mob" id="popen"><svg viewBox="0 0 16 16"><path d="M1 1h4v4H1zm5 0h4v4H6zm5 0h4v4h-4zM1 6h4v4H1zm5 0h4v4H6zm5 0h4v4h-4zM1 11h4v4H1zm5 0h4v4H6zm5 0h4v4h-4z"/></svg>Palette</button></span></div>
   <div class="mtools"><button class="b b-wh" id="bback2" type="button">‹ Back</button><button class="b b-wh" id="bclr2" type="button">Clear Response</button></div>
   <div class="qa" id="qa"></div>
   <div class="acts"><div class="l"><button class="b b-mk" id="bmark">Mark for Review &amp; Next</button><button class="b b-wh" id="bclr">Clear Response</button></div>
    <div class="r"><button class="b b-wh" id="bback">‹ Back</button><button class="b b-pr" id="bsave">Save &amp; Next ›</button></div></div>
  </div>
  <aside class="side" id="side">
   <div class="grab"><span>Question Palette</span><button id="pclose" aria-label="बंद करें">×</button></div>
   <div class="cand"><div class="av"><?php echo $eh_ph; ?></div><div><b class="cn"></b><span>Roll No: <span class="roll"></span></span></div></div>
   <div class="lgd" id="lgd"></div>
   <div class="pview">You are viewing <b id="pvs"></b> section</div>
   <div class="palg" id="palg"></div>
   <div class="ft"><button class="b b-wh" id="bqp">Question Paper</button><button class="b b-wh" id="bins">Instructions</button><button class="b b-pr" id="bsub">Submit</button></div>
  </aside>
 </div>
 <div class="shade" id="shade"></div>
</section>

<!-- ===== result ===== -->
<section class="scr" id="s-res">
 <?php $eh_hd('Result · Score Card', '', true); ?>
 <div id="resw"></div>
</section>

<!-- ===== solutions ===== -->
<section class="scr" id="s-sol">
 <div class="top"><button class="b b-wh" id="solback">‹ रिज़ल्ट</button><h3>प्रश्न एवं व्याख्या</h3><select id="lsel2" style="margin-left:auto;padding:7px 10px;border-radius:8px;border:1px solid var(--ln);font-family:inherit"><option value="hi">हिंदी</option><option value="en">English</option></select></div>
 <div class="wrap" style="padding-top:12px"><div class="filt" id="filt"></div><div id="sols"></div></div>
</section>

<!-- ===== admin ===== -->
<section class="scr" id="s-adm">
 <?php $eh_hd('Admin · Live Monitor'); ?>
 <div class="adm"><div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px"><h2 style="margin:0;color:var(--nv);font-size:19px" id="a-t"></h2><div style="display:flex;gap:8px;align-items:center"><select id="a-r" style="padding:8px 10px;border-radius:8px;border:1px solid var(--ln);font-family:inherit"></select><button class="b b-wh" id="a-csv">⬇ CSV</button></div></div>
 <div class="ak" id="a-k"></div><div class="card"><div class="tw"><table class="t" id="a-tb"></table></div><p style="font-size:12px;color:var(--mu);margin:10px 0 0">हर 10 सेकंड में अपने आप अपडेट · "दे रहे" = पिछले 2 मिनट में सक्रिय</p></div></div>
</section>

<div class="mdl" id="mdl"><div class="bx"><h3><span id="mt"></span><button id="mx" aria-label="बंद करें">×</button></h3><div class="in" id="mb"></div><div class="ftr" id="mf"></div></div></div>
<div id="printArea"></div>
<div class="pdfwait" id="pdfwait"><div class="pc"></div><div id="pdfmsg">PDF बन रही है…</div></div>
<div id="pdfStage"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>

<script>
{
const C=<?php echo wp_json_encode($eh_cfg); ?>;
const PH=<?php echo wp_json_encode($eh_ph); ?>;
const r=(a,b)=>Array.from({length:b-a+1},(_,i)=>a-1+i);
const SECS=[["सामान्य ज्ञान एवं समसामयिकी",r(1,25)],["तार्किक ज्ञान",r(26,30)],["हिंदी, संस्कृत एवं अंग्रेजी",r(31,60)],["विज्ञान",r(61,68)],["गणित",r(69,84)],["पर्यावरण एवं सामाजिक अध्ययन",r(85,92)],["शिक्षण कौशल",r(93,100)],["बाल मनोविज्ञान",r(101,108)],["सूचना तकनीक (ICT)",r(109,112).concat(r(117,120))],["जीवन कौशल / प्रबंधन",r(113,116)]].map(x=>({n:x[0],q:x[1]}));
const L=['A','B','C','D'],$=id=>document.getElementById(id);
const esc=s=>String(s==null?'':s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
let off=C.now*1000-Date.now(),R=C.round,A=null,Q=[],ME={name:'',roll:''},sel=null,T=null,BG=null,dirty=false,saving=false,qStart=Date.now(),subbing=false,RS=null,AR='',hidAt=0,LANG='hi';try{LANG=localStorage.getItem('eh_stm_lang')||'hi'}catch(e){}
const QT=q=>LANG==='en'&&q.qe?q.qe:q.q,QO=q=>LANG==='en'&&q.oe?q.oe:q.o;
function setLang(v){LANG=v==='en'?'en':'hi';try{localStorage.setItem('eh_stm_lang',LANG)}catch(e){}['lsel','lsel0'].forEach(id=>{const e=document.getElementById(id);if(e)e.value=LANG})}
const now=()=>Math.floor((Date.now()+off)/1000);
const ist=(t,o)=>new Date(t*1000).toLocaleString('hi-IN',Object.assign({timeZone:'Asia/Kolkata'},o));
const tStr=t=>new Date(t*1000).toLocaleTimeString('en-IN',{timeZone:'Asia/Kolkata',hour:'numeric',minute:'2-digit'}).toUpperCase();
const fmt=s=>{s=Math.max(0,s);const h=Math.floor(s/3600),m=Math.floor(s%3600/60),x=s%60;return h+':'+String(m).padStart(2,'0')+':'+String(x).padStart(2,'0')};
const mins=s=>{s=Math.round(s);const m=Math.floor(s/60);return m?m+' मिनट '+(s%60)+' सेकंड':s+' सेकंड'};
function api(p,body,keep){return fetch(C.rest+p,{method:body?'POST':'GET',credentials:'same-origin',keepalive:!!keep,headers:Object.assign({'X-WP-Nonce':C.nonce},body?{'Content-Type':'application/json'}:{}),body:body?JSON.stringify(body):undefined}).then(x=>x.json().then(j=>{if(!x.ok)throw j;return j}))}
function go(id){document.querySelectorAll('.scr').forEach(s=>s.classList.toggle('on',s.id===id));window.scrollTo(0,0)}
function modal(t,body,btns){$('mt').textContent=t;$('mb').innerHTML=body;$('mf').innerHTML='';(btns||[['ठीक है','b-pr',close]]).forEach(b=>{const e=document.createElement('button');e.className='b '+b[1];e.textContent=b[0];e.onclick=b[2];$('mf').appendChild(e)});$('mx').style.display=btns&&!btns.length?'none':'';$('mdl').classList.add('on')}
function close(){$('mdl').classList.remove('on')}
$('mx').onclick=close;
document.querySelectorAll('[data-go]').forEach(b=>b.onclick=()=>go(b.dataset.go));
const pat=SECS.map(s=>`<tr><td>${esc(s.n)}</td><td>${s.q.length}</td><td>${s.q.length*3}</td></tr>`).join('')+'<tr><th>कुल</th><th>120</th><th>360</th></tr>';
$('pat').innerHTML=pat;$('pat2').innerHTML=pat;

/* ---------- home ---------- */
function ring(v,max,l){const c=2*Math.PI*40,o=c*(1-Math.min(1,v/max));return `<div class="ring"><svg viewBox="0 0 92 92"><circle class="bgc" cx="46" cy="46" r="40"/><circle class="fgc" cx="46" cy="46" r="40" stroke-dasharray="${c.toFixed(1)}" stroke-dashoffset="${o.toFixed(1)}"/></svg><div><b>${String(v).padStart(2,'0')}</b><span>${l}</span></div></div>`}
function status(){const n=now();return n<R.login?'soon':n<R.start?'login':n<R.end?'live':'over'}
function home(){const n=now(),st=status();
 $('h-date').textContent=ist(R.start,{weekday:'long',day:'numeric',month:'long',year:'numeric'});
 $('h-time').textContent=tStr(R.start)+' – '+tStr(R.end);$('cf-date').textContent=ist(R.start,{day:'numeric',month:'long',year:'numeric'})+' · '+tStr(R.start)+' – '+tStr(R.end);
 const live=st==='live';$('h-chip').classList.toggle('live',live);$('h-chipt').textContent=live?'LIVE · मॉक रात 11:59 तक खुला है':st==='over'?'परीक्षा समाप्त':'लाइव मॉक टेस्ट';
 const left=st==='over'?0:Math.max(0,(live?R.end:R.start)-n);$('cd-lbl').textContent=st==='over'?'मॉक समाप्त हो चुका है':live?'मॉक बंद होने में':'मॉक खुलने में';
 const d=Math.floor(left/86400),h=Math.floor(left%86400/3600),m=Math.floor(left%3600/60),s=left%60;
 $('cd').innerHTML=(d?ring(d,30,'दिन'):'')+ring(h,24,'घंटे')+ring(m,60,'मिनट')+ring(s,60,'सेकंड');
 const tl=[[R.login,'लॉग-इन व निर्देश','उम्मीदवार विवरण जाँचें, निर्देश पढ़ें'],[R.start,'मॉक खुलेगा','रात 11:59 तक कभी भी शुरू करें · 120 मिनट'],[R.end,'मॉक बंद','इसके बाद नया प्रयास नहीं'],[R.end,'रिज़ल्ट','स्कोर सबमिट के तुरंत बाद · अंतिम रैंक/परसेंटाइल समाप्ति के बाद']];
 const th=tl.map((x,i)=>`<li class="${n>=x[0]&&i<3?'on':''}"><span class="t">${tStr(x[0]).replace(' ','<br>')}</span><div><b>${x[1]}</b><span>${x[2]}</span></div></li>`).join('');if($('tl').innerHTML!==th)$('tl').innerHTML=th;
 cta(st)}
function cta(st){let h='',p='',b='';const a=A;
 if(!C.in){h='परीक्षा देने के लिए लॉग-इन करें';p='xlstudy.in का अकाउंट नहीं है तो 1 मिनट में नया अकाउंट बनाएँ।';b=`<a class="b b-wh b-lg" href="${esc(C.signup)}">नया अकाउंट</a><a class="b b-gd b-lg" href="${esc(C.login)}">लॉग-इन करें</a>`}
 else if(!a){h='लोड हो रहा है…'}
 else if(+a.submitted){h=ME.name+', आपका प्रयास '+(a.att||1)+' सबमिट हो चुका है';p='रिज़ल्ट देखें, या दोबारा मॉक दें — रैंक में आपका नया स्कोर गिना जाएगा।';b='<button class="b b-wh b-lg" id="c-re"'+(st==='live'?'':' disabled')+'>↻ दोबारा दें</button><button class="b b-gd b-lg" id="c-go">रिज़ल्ट देखें ›</button>'}
 else if(+a.started){h='आपकी परीक्षा जारी है';p='बचा समय '+fmt(a.deadline-now())+' · आपके उत्तर सर्वर पर सेव हैं।';b='<button class="b b-gd b-lg" id="c-go">परीक्षा जारी रखें ›</button>'}
 else if(st==='soon'){h='नमस्ते, '+ME.name;p='लॉग-इन '+tStr(R.login)+' से खुलेगा। तब तक नीचे पैटर्न और निर्देश देख लें।';b='<button class="b b-wh b-lg" disabled>'+tStr(R.login)+' से खुलेगा</button>'}
 else if(st==='over'){h='यह परीक्षा समाप्त हो चुकी है';p='प्रश्न-पत्र और उत्तर-कुंजी डाउनलोड करें।';b='<button class="b b-pr b-lg" id="c-paper">⬇ प्रश्न-पत्र + उत्तर-कुंजी</button>'}
 else {h='नमस्ते, '+ME.name;p=st==='live'?(R.end-now()>=7200?'मॉक खुला है। शुरू करने पर आपको पूरे 120 मिनट मिलेंगे।':'मॉक बंद होने वाला है — अभी शुरू करने पर केवल '+fmt(R.end-now())+' मिलेंगे।'):'विवरण जाँचें और निर्देश पढ़ें। परीक्षा '+tStr(R.start)+' पर शुरू होगी।';b='<button class="b b-gd b-lg" id="c-go">परीक्षा में प्रवेश करें ›</button>'}
 if($('c-h').textContent!==h)$('c-h').textContent=h;if($('c-p').textContent!==p)$('c-p').textContent=p;
 if($('c-bt').dataset.k!==b){$('c-bt').innerHTML=b;$('c-bt').dataset.k=b;const g=$('c-go');if(g)g.onclick=enter;const re=$('c-re');if(re)re.onclick=retry;const pp=$('c-paper');if(pp)pp.onclick=paper}}
let RETRY=0;function retry(){if(status()!=='live')return;RETRY=1;$('agree').checked=false;clearInterval(LT);go('s-conf')}
function enter(){RETRY=0;if(+A.submitted)return showResult();if(+A.started)return start();go('s-conf')}
function names(){document.querySelectorAll('.cn').forEach(e=>e.textContent=ME.name);document.querySelectorAll('.roll').forEach(e=>e.textContent=ME.roll)}

/* ---------- begin ---------- */
function waitTick(){const n=now(),b=$('begin');if(n<R.start){b.disabled=true;$('wait').innerHTML='परीक्षा शुरू होने में <b>'+fmt(R.start-n)+'</b>'}else if(n>=R.end){b.disabled=true;$('wait').textContent='परीक्षा समाप्त हो चुकी है'}else{b.disabled=false;$('wait').textContent=''}}
$('begin').onclick=()=>{if(!$('agree').checked){$('ag-err').textContent='आगे बढ़ने के लिए घोषणा पर ✓ लगाएँ';return}
 $('begin').disabled=true;$('ag-err').textContent='';api('start',{retry:RETRY}).then(j=>{off=j.now*1000-Date.now();A=j.attempt;RETRY=0;subbing=false;dirty=false;start()}).catch(e=>{$('begin').disabled=false;$('ag-err').textContent=(e&&e.message)||'नेटवर्क त्रुटि, दोबारा प्रयास करें'})};

/* ---------- exam ---------- */
const secOf=i=>SECS.findIndex(s=>s.q.includes(i));
const stOf=i=>A.st[i]||'nv';
function start(){Q=A.q||[];if(!Q.length){location.reload();return}names();go('s-exam');show(+A.cur||0);tick();clearInterval(T);T=setInterval(tick,1000);
 clearInterval(BG);BG=setInterval(()=>{push().catch(()=>{})},20000);}
function tick(){const l=A.deadline-now();$('tm').textContent=fmt(l);$('tbox').classList.toggle('low',l<=300&&l>0);if(l<=0&&!+A.submitted)submit(true)}
function acc(){const i=A.cur,d=Math.round((Date.now()-qStart)/1000);if(d>0&&d<3600)A.tq[i]=(+A.tq[i]||0)+d;qStart=Date.now()}
function sv(c,t){const e=$('sv');e.className='sv '+c;e.textContent=t}
function push(fin,keep){acc();if(saving&&!fin){dirty=true;return Promise.resolve()}saving=true;dirty=false;sv('w','Saving…');
 return api('save',{ans:A.ans,st:A.st,tq:A.tq,cur:A.cur,tabs:A.tabs,submit:fin?1:0},keep).then(j=>{saving=false;sv('','Saved');if(j.now)off=j.now*1000-Date.now();if(j.submitted&&!fin&&!+A.submitted&&!subbing){A.submitted=1;clearInterval(T);clearInterval(BG);modal('परीक्षा समाप्त','<p style="margin:0">मॉक बंद कर दिया गया है, आपका पेपर अपने आप सबमिट हो गया है।</p>');showResult()}return j}).catch(e=>{saving=false;dirty=true;sv('x','Offline');throw e})}
const LK=()=>'eh_stm_'+R.id+'_'+ME.roll+'_'+(A&&A.att||1);
function backup(){try{localStorage.setItem(LK(),JSON.stringify({ans:A.ans,st:A.st,tq:A.tq,cur:A.cur,tabs:A.tabs,t:now()}))}catch(e){}}
let pt=null;function later(){dirty=true;backup();clearTimeout(pt);pt=setTimeout(()=>push().catch(()=>{}),700)}
document.addEventListener('visibilitychange',()=>{if(!A||!+A.started||+A.submitted||!$('s-exam').classList.contains('on'))return;
 if(document.hidden){hidAt=Date.now();push(false,true).catch(()=>{})}else if(hidAt&&Date.now()-hidAt>1500){hidAt=0;A.tabs=(+A.tabs||0)+1;later();modal('चेतावनी','<p style="line-height:1.7;margin:0">परीक्षा के दौरान दूसरी टैब या ऐप खोलना वर्जित है। यह गिनती दर्ज कर ली गई है। (कुल: <b>'+A.tabs+'</b>)</p>')}});
window.addEventListener('online',()=>{if(dirty&&A&&!+A.submitted)push().catch(()=>{})});
function secs(){const k=secOf(A.cur);$('secs').innerHTML=SECS.map((s,i)=>{const a=s.q.filter(j=>/an|am/.test(stOf(j))).length;return `<button class="${i===k?'on':''}" data-s="${i}">${esc(s.n)}<small>${a}/${s.q.length}</small></button>`}).join('');
 const on=$('secs').querySelector('.on');if(on)on.scrollIntoView({inline:'center',block:'nearest'})}
$('secs').onclick=e=>{const b=e.target.closest('[data-s]');if(b)show(SECS[+b.dataset.s].q[0])};
function show(i){if(A.cur!==i)acc();A.cur=i;qStart=Date.now();if(stOf(i)==='nv')A.st[i]='na';sel=A.ans[i]||null;const q=Q[i];
 $('qno').textContent='Question No. '+(i+1);
 let h='';if(q.p)h+='<div class="pass"><b>गद्यांश / Passage</b>'+esc(q.p.replace(/^(निर्देश|Direction)[^:]*:\s*/,''))+'</div>';
 h+='<div class="qt">'+esc(QT(q))+'</div>'+QO(q).map((o,k)=>`<label class="op${sel===L[k]?' sel':''}"><input type="radio" name="op" value="${L[k]}"${sel===L[k]?' checked':''}><span class="k">${k+1}</span><span>${esc(o)}</span></label>`).join('');
 $('qa').innerHTML=h;$('qa').scrollTop=0;secs();pal();later()}
$('qa').onchange=e=>{if(e.target.name==='op'){sel=e.target.value;document.querySelectorAll('.op').forEach(o=>o.classList.toggle('sel',o.querySelector('input').checked))}};
function next(){const k=secOf(A.cur),s=SECS[k].q,p=s.indexOf(A.cur);if(p<s.length-1)show(s[p+1]);else if(k<SECS.length-1)show(SECS[k+1].q[0]);else{secs();pal();later()}}
$('bsave').onclick=()=>{const i=A.cur;if(sel){A.ans[i]=sel;A.st[i]='an'}else{delete A.ans[i];A.st[i]='na'}next()};
$('bmark').onclick=()=>{const i=A.cur;if(sel){A.ans[i]=sel;A.st[i]='am'}else{delete A.ans[i];A.st[i]='mr'}next()};
$('bclr').onclick=()=>{const i=A.cur;sel=null;delete A.ans[i];A.st[i]='na';show(i)};
$('bback2').onclick=()=>$('bback').click();$('bclr2').onclick=()=>$('bclr').click();
$('bback').onclick=()=>{const k=secOf(A.cur),s=SECS[k].q,p=s.indexOf(A.cur);if(p>0)show(s[p-1]);else if(k>0){const t=SECS[k-1].q;show(t[t.length-1])}};
function cnt(list){const c={an:0,na:0,nv:0,mr:0,am:0};list.forEach(i=>c[stOf(i)]++);return c}
function pal(){const k=secOf(A.cur),s=SECS[k],c=cnt(s.q);
 $('lgd').innerHTML=`<div><span class="q an">${c.an}</span>Answered</div><div><span class="q na">${c.na}</span>Not Answered</div><div><span class="q">${c.nv}</span>Not Visited</div><div><span class="q mr">${c.mr}</span>Marked for Review</div><div class="w"><span class="q am">${c.am}</span>Answered &amp; Marked for Review (will be considered for evaluation)</div>`;
 $('pvs').textContent=s.n;$('palg').innerHTML=s.q.map(i=>`<button class="q ${stOf(i)}${i===A.cur?' cur':''}" data-q="${i}">${i+1}</button>`).join('')}
$('palg').onclick=e=>{const b=e.target.closest('[data-q]');if(b){show(+b.dataset.q);side(0)}};
function side(o){$('side').classList.toggle('open',!!o);$('shade').classList.toggle('on',!!o)}
$('popen').onclick=()=>side(1);$('pclose').onclick=()=>side(0);$('shade').onclick=()=>side(0);
$('bins').onclick=()=>{side(0);modal('Instructions','<ul style="line-height:1.9;margin:0;padding-left:20px"><li>120 प्रश्न · 120 मिनट · 360 अंक</li><li>सही उत्तर +3, गलत उत्तर −1, छोड़ा 0</li><li>उत्तर सेव करने के लिए Save &amp; Next दबाएँ</li><li>किसी भी सेक्शन में कभी भी जा सकते हैं</li><li>समय समाप्त होने पर परीक्षा अपने आप सबमिट होगी</li></ul>')};
$('bqp').onclick=()=>{side(0);modal('Question Paper',Q.map((q,i)=>`<p style="margin:0 0 10px;line-height:1.7"><b>${i+1}.</b> ${esc(QT(q))}</p>`).join(''))};
$('lsel').onchange=e=>{setLang(e.target.value);show(A.cur)};$('lsel0').onchange=e=>setLang(e.target.value);setLang(LANG);
$('bend').onclick=()=>$('bsub').click();
$('bsub').onclick=()=>{side(0);const rows=SECS.map(s=>{const c=cnt(s.q);return `<tr><td>${esc(s.n)}</td><td>${s.q.length}</td><td>${c.an+c.am}</td><td>${c.na}</td><td>${c.mr+c.am}</td><td>${c.nv}</td></tr>`}).join('');
 modal('Exam Summary',`<div class="tw"><table class="t"><thead><tr><th>Section</th><th>Questions</th><th>Answered</th><th>Not Answered</th><th>Marked</th><th>Not Visited</th></tr></thead>${rows}</table></div><p style="margin:14px 0 0;font-weight:600;line-height:1.7">बचा समय: <span class="num">${fmt(A.deadline-now())}</span><br>क्या आप परीक्षा सबमिट करना चाहते हैं? सबमिट के बाद उत्तर बदले नहीं जा सकेंगे।</p>`,[['No','b-wh',close],['Yes, Submit','b-pr',()=>{close();submit(false)}]])};
function submit(auto){if(subbing)return;subbing=true;clearInterval(T);clearInterval(BG);
 modal(auto?'समय समाप्त':'सबमिट हो रहा है…','<p style="margin:0;line-height:1.7">'+(auto?'आपका समय पूरा हो गया। ':'')+'आपके उत्तर सर्वर पर भेजे जा रहे हैं, कृपया प्रतीक्षा करें…</p>',[]);
 const tryIt=n=>push(true).then(()=>{A.submitted=1;try{if(document.fullscreenElement)document.exitFullscreen()}catch(e){}showResult()}).catch(()=>{if(n<40)setTimeout(()=>tryIt(n+1),3000);else{subbing=false;modal('नेट नहीं है','<p style="margin:0">इंटरनेट जोड़कर पेज दोबारा खोलें। आपके उत्तर सुरक्षित हैं।</p>')}});
 tryIt(0)}

/* ---------- result ---------- */
let LT=null;
function showResult(){go('s-res');$('resw').innerHTML='<p style="text-align:center;padding:60px 20px">रिज़ल्ट तैयार हो रहा है…</p>';
 api('result').then(renderRes).catch(()=>{close();$('resw').innerHTML='<p style="text-align:center;padding:60px 20px">रिज़ल्ट लोड नहीं हुआ। <button class="b b-pr" onclick="location.reload()">दोबारा</button></p>'})}
function perSec(d){const ans=d.ans||{},tq=d.tq||{},k=d.key;return SECS.map(s=>{let c=0,w=0,t=0;s.q.forEach(i=>{const a=ans[i];t+=+tq[i]||0;if(!a)return;a===k[i]?c++:w++});return {n:s.n,len:s.q.length,c,w,m:c*3-w,t}})}
const TGI='<svg viewBox="0 0 24 24"><path d="M21.5 4.2 2.9 11.4c-1.3.5-1.3 1.2-.2 1.5l4.8 1.5 1.8 5.6c.2.6.4.8.9.8.4 0 .6-.2.9-.4l2.3-2.2 4.8 3.5c.9.5 1.5.2 1.7-.8l3.2-15c.3-1.3-.5-1.9-1.6-1.2zM8.6 14.1l9.6-6c.5-.3.9-.1.5.2l-7.9 7.2-.3 3.2-1.9-4.6z"/></svg>';
function joinHTML(){return `<div class="join"><a href="${esc(C.tgc)}" target="_blank" rel="noopener"><i>${TGI}</i><div><b>UPSSSC ALERT चैनल</b><span>टेलीग्राम चैनल ज्वाइन करें</span></div></a><a class="g" href="${esc(C.tgg)}" target="_blank" rel="noopener"><i>${TGI}</i><div><b>UPSSSC ALERT ग्रुप</b><span>टेलीग्राम ग्रुप ज्वाइन करें</span></div></a></div>`}
function renderRes(d){RS=d;close();
 const sk=120-d.correct-d.wrong,att=d.correct+d.wrong,ac=att?Math.round(d.correct*100/att):0,per=perSec(d),tt=Math.max(1,per.reduce((a,p)=>a+p.t,0));
 const C2=2*Math.PI*72;
 let h=`<div class="rhero"><div class="in"><div class="av big">${PH}</div><div><h2>${esc(d.name)}</h2><p>रोल नं. <span class="num">${esc(d.roll)}</span> · UP Super TET 2026 लाइव मॉक · लगा समय ${mins(d.used)}${d.tabs?' · टैब बदले '+d.tabs:''}</p>
 <div class="hstat"><span>अंक <b>${d.score}</b> / 360</span><span>परसेंटाइल <b id="hp">${d.percentile.toFixed(2)}</b></span><span>रैंक <b id="hr">${d.rank}</b> / <i id="ht" style="font-style:normal">${d.total}</i></span><span>प्रयास <b>${d.att||1}</b></span></div></div></div></div>
 <div class="wrap"><div class="rtop"><div class="gauge"><svg viewBox="0 0 170 170"><circle class="bgc" cx="85" cy="85" r="72"/><circle class="fgc" id="gfg" cx="85" cy="85" r="72" stroke-dasharray="${C2.toFixed(1)}" stroke-dashoffset="${C2.toFixed(1)}"/></svg><div><b>${d.score}</b><span>कुल अंक / 360</span></div></div>
 <div class="kps"><div class="kp"><i class="i1">%</i><div><b id="gp">${d.percentile.toFixed(2)}</b><span>परसेंटाइल</span></div></div>
 <div class="kp"><i class="i2">#</i><div><b><span id="kr">${d.rank}</span><small style="font-size:13px;color:var(--mu)"> / <span id="kt">${d.total}</span></small></b><span>रैंक</span></div></div>
 <div class="kp"><i style="background:#f0e9fb;color:var(--pu)">◎</i><div><b>${ac}%</b><span>सटीकता</span></div></div>
 <div class="kp"><i class="i3">✓</i><div><b>${d.correct}</b><span>सही (+${d.correct*3})</span></div></div>
 <div class="kp"><i class="i4">✗</i><div><b>${d.wrong}</b><span>गलत (−${d.wrong})</span></div></div>
 <div class="kp"><i style="background:#eef2f7;color:#5b6675">—</i><div><b>${sk}</b><span>छोड़े</span></div></div></div></div>
 <div class="note" id="rnote"></div>
 <div class="card" style="margin-top:16px"><h2>किस अंक पर कितने उम्मीदवार <span class="livebadge" id="lvb">LIVE</span></h2><div class="dist" id="dist"></div><p style="font-size:12.5px;color:var(--mu);margin:8px 0 0" id="dnote"></p></div>
 <div class="grid2" style="margin-top:16px"><div class="card cmp"><h2>तुलना</h2><div id="cmp"></div>
  <h2 style="margin-top:18px">समय का उपयोग</h2>${per.map(p=>`<div class="row" style="grid-template-columns:minmax(0,1.3fr) minmax(0,1fr) 58px;font-size:12.5px"><span>${esc(p.n)}</span><div class="track"><i style="width:${p.t*100/tt}%;background:var(--nv2)"></i></div><span class="num" style="text-align:right">${Math.floor(p.t/60)}:${String(p.t%60).padStart(2,'0')}</span></div>`).join('')}
 </div><div class="card"><h2>टॉप 10</h2><ol class="lb" id="top"></ol></div></div>
 <div class="card" style="margin-top:16px"><h2>सेक्शन-वार प्रदर्शन</h2><div class="lgnd"><span style="--c:var(--gr)">सही</span><span style="--c:var(--rd)">गलत</span><span style="--c:#dfe6ee">छोड़े</span></div>
  ${per.map(p=>`<div class="secr"><div>${esc(p.n)}<small>${p.c} सही · ${p.w} गलत · ${p.len-p.c-p.w} छोड़े (कुल ${p.len})</small></div><div class="track"><i style="width:${p.c*100/p.len}%;background:var(--gr)"></i><i style="width:${p.w*100/p.len}%;background:var(--rd)"></i></div><span class="m">${p.m}<small style="display:inline;font-weight:400"> /${p.len*3}</small></span></div>`).join('')}</div>
 ${(d.hist||[]).length>1?`<div class="card" style="margin-top:16px"><h2>आपके सभी प्रयास</h2><div class="atts">${d.hist.map(x=>`<div class="${x[0]===d.att?'on':''}"><b>${x[1]}</b><span>प्रयास ${x[0]}</span></div>`).join('')}</div><p style="font-size:12.5px;color:var(--mu);margin:8px 0 0">रैंक और परसेंटाइल में आपका सबसे नया प्रयास गिना जाता है।</p></div>`:''}
 <button class="solbtn" id="vsol"><i>📖</i><div><b>प्रश्न एवं व्याख्या देखें</b><span>सभी 120 प्रश्न · आपका उत्तर · सही उत्तर · व्याख्या</span></div><em>›</em></button>
 <div style="display:flex;gap:10px;flex-wrap:wrap;margin:0 0 6px"><button class="b b-pr b-lg" id="pdf" style="flex:1">⬇ रिज़ल्ट + प्रश्न-पत्र PDF डाउनलोड</button><button class="b b-gd b-lg" id="rretry">↻ दोबारा दें (प्रयास ${(d.att||1)+1})</button><button class="b b-wh b-lg" id="rhome">होम</button></div>
 ${joinHTML()}
 <div class="promo"><div class="pt"><span class="tagp">UPSSSC ALERT · पेड बैच</span><h3>आचार्य 2.0 <em>Super TET</em></h3><p>रैपिड रिवीजन बैच — परीक्षा से पहले पूरा सिलेबस दोबारा, आपकी कमज़ोर सेक्शन पर ज़ोर।</p>
  <ul><li>सभी विषयों के PDF नोट्स</li><li>टॉपिक-वाइज और मिक्स प्रैक्टिस क्विज</li><li>10 फुल लेंथ टेस्ट</li><li>पिछले वर्षों के प्रश्न (PYQ)</li></ul>
  <div class="pr"><div class="fee"><small>बैच फीस</small>₹199</div><a class="b b-gd b-lg" href="${esc(C.tgj)}" target="_blank" rel="noopener">अभी जुड़ें ›</a><a class="b b-wa" href="${esc(C.wa)}" target="_blank" rel="noopener">WhatsApp</a></div></div>
  <div class="pb"><img src="${esc(C.book)}" alt="UP SUPER TET Book" loading="lazy"><b>UP SUPER TET बुक · ₹429</b><span>6,000+ MCQ · थ्योरी · मॉडल पेपर · घर तक डिलीवरी</span><div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center"><a class="b b-wh" href="${esc(C.booktg)}" target="_blank" rel="noopener">ऑर्डर करें</a><a class="b b-wa" href="${esc(C.bookwa)}" target="_blank" rel="noopener">WhatsApp</a></div></div></div>
 <div class="foot"><img src="${esc(C.logo)}" alt=""> UPSSSC ALERT × XL Study · xlstudy.in</div></div>`;
 $('resw').innerHTML=h;liveUpd(d,true);
 $('pdf').onclick=()=>makePdf(RS);$('rretry').onclick=()=>{if(status()!=='live'){modal('सूचना','<p style="margin:0">मॉक बंद हो चुका है, अब नया प्रयास नहीं हो सकता।</p>');return}retry()};if(status()!=='live')$('rretry').disabled=true;$('rhome').onclick=()=>{clearInterval(LT);go('s-home');home()};
 $('vsol').onclick=()=>{fl('all');go('s-sol')};
 clearInterval(LT);LT=setInterval(()=>{if(!$('s-res').classList.contains('on'))return;api('live').then(x=>liveUpd(x,false)).catch(()=>{})},20000)}
function liveUpd(x,first){const d=RS;Object.assign(d,x);
 const C2=2*Math.PI*72;setTimeout(()=>{const g=$('gfg');if(g)g.style.strokeDashoffset=C2*(1-Math.max(0,d.score)/360)},first?80:0);
 $('gp').textContent=d.percentile.toFixed(2);$('hp').textContent=d.percentile.toFixed(2);$('hr').textContent=d.rank;$('kr').textContent=d.rank;$('ht').textContent=d.total;$('kt').textContent=d.total;
 $('rnote').innerHTML=d.final?'✅ परीक्षा समाप्त — यह अंतिम रैंक और परसेंटाइल है।':'⏳ परीक्षा अभी चल रही है ('+d.writing+' उम्मीदवार अभी पेपर दे रहे हैं)। रैंक और परसेंटाइल अभी तक सबमिट हुए <b>'+d.total+'</b> उम्मीदवारों के आधार पर है और हर 20 सेकंड में अपडेट होती है।';
 $('lvb').style.display=d.final?'none':'';
 const mx=Math.max(1,d.topper,d.score);$('cmp').innerHTML=[['आपका स्कोर',d.score,'var(--pr)'],['औसत स्कोर',d.avg,'#9aa8b8'],['टॉपर',d.topper,'var(--gd)']].map(x=>`<div class="row"><span>${x[0]}</span><div class="track"><i style="width:${Math.max(0,x[1])*100/mx}%;background:${x[2]}"></i></div><b class="num" style="text-align:right">${x[1]}</b></div>`).join('');
 $('top').innerHTML=(d.top||[]).map((x,i)=>`<li><i>${i+1}</i><span>${esc(x.n)}</span><b>${x.s}</b></li>`).join('')||'<li>अभी कोई नहीं</li>';
 const B={},step=10;(d.dist||[]).forEach(([s,c])=>{const b=Math.floor(s/step)*step;B[b]=(B[b]||0)+c});
 const keys=Object.keys(B).map(Number);const mine=Math.floor(d.score/step)*step;keys.push(mine);
 const lo=Math.min(0,...keys),hi=Math.max(...keys),bm=Math.max(1,...Object.values(B));let bars='',lbl='';
 for(let b=lo;b<=hi;b+=step){const c=B[b]||0,me=b===mine;bars+=`<div class="bc${me?' me':''}" title="${b} से ${b+step-1} अंक: ${c}"><b>${c||''}</b><i style="height:${c?Math.max(4,c*150/bm):0}px"></i></div>`;lbl+=`<span class="${me?'me':''}">${b}</span>`}
 $('dist').innerHTML=`<div class="bars">${bars}</div><div class="lb2">${lbl}</div>`;
 $('dnote').innerHTML='हर बार 10 अंकों का समूह है (जैसे 120 = 120 से 129 अंक)। <b style="color:#a87612">सुनहरा बार</b> = आपका स्कोर। कुल '+d.total+' उम्मीदवार।';
 if(first){const me=$('dist').querySelector('.me');if(me)$('dist').scrollLeft=me.offsetLeft-$('dist').clientWidth/2}}
let FL='all';
function fl(f){FL=f;$('lsel2').value=LANG;const d=RS,sk=120-d.correct-d.wrong;$('filt').innerHTML=[['all','सभी (120)'],['c','सही ('+d.correct+')'],['w','गलत ('+d.wrong+')'],['s','छोड़े ('+sk+')']].map(x=>`<button class="${x[0]===f?'on':''}" data-f="${x[0]}">${x[1]}</button>`).join('');sols(f);window.scrollTo(0,0)}
$('filt').onclick=e=>{const b=e.target.closest('[data-f]');if(b)fl(b.dataset.f)};
$('solback').onclick=()=>go('s-res');$('lsel2').onchange=e=>{setLang(e.target.value);sols(FL)};
function solHTML(d,i,f){const ans=d.ans||{},q=d.q[i],a=ans[i],k=!a?'s':(a===d.key[i]?'c':'w');if(f&&f!=='all'&&f!==k)return '';
 const tg={s:'<span class="tg s">छोड़ा</span>',c:'<span class="tg c">सही +3</span>',w:'<span class="tg w">गलत −1</span>'}[k];
 return `<div class="sol"><div class="h"><span>प्रश्न ${i+1} · ${esc(SECS[secOf(i)].n)}</span>${tg}</div>${q.p?'<div style="color:var(--mu);font-size:13px">[गद्यांश आधारित]</div>':''}<div>${esc(QT(q))}</div>${QO(q).map((o,j)=>`<div class="o ${L[j]===d.key[i]?'ok':(L[j]===a?'bad':'')}">${j+1}. ${esc(o)}${L[j]===d.key[i]?' ✓':(L[j]===a?' ✗ (आपका उत्तर)':'')}</div>`).join('')}${d.exp[i]?`<div class="ex"><b>व्याख्या:</b> ${esc(d.exp[i])}</div>`:''}</div>`}
function sols(f){$('sols').innerHTML=RS.q.map((q,i)=>solHTML(RS,i,f)).join('')||'<p style="text-align:center;color:var(--mu);padding:30px">कोई प्रश्न नहीं</p>'}

/* ---------- PDF (one click download, with details + watermark + join links) ---------- */
function libs(){return new Promise((ok,no)=>{let n=0;const t=setInterval(()=>{if(window.html2canvas&&window.jspdf){clearInterval(t);ok()}else if(++n>150){clearInterval(t);no()}},100)})}
async function makePdf(d){const W=$('pdfwait'),M=$('pdfmsg');W.classList.add('on');M.textContent='PDF बन रही है…';
 try{await libs();if(document.fonts&&document.fonts.ready)await document.fonts.ready;
  const st=$('pdfStage'),blocks=[],css=`<style>#pdfStage *{box-sizing:border-box}#pdfStage .pb{padding:10px 26px}#pdfStage h1{font-size:22px;margin:0;color:#0f2a4d}#pdfStage table{width:100%;border-collapse:collapse;font-size:14px}#pdfStage td,#pdfStage th{border:1px solid #dbe3ec;padding:6px 9px;text-align:left}#pdfStage th{background:#f2f5f9}#pdfStage .qq{font-size:14.5px;line-height:1.65;border:1px solid #e3e9f0;border-radius:10px;padding:10px 12px}#pdfStage .o{padding:2px 8px;border-radius:6px}#pdfStage .ok{background:#e3f5e6;color:#14532d;font-weight:700}#pdfStage .bad{background:#fde6e2;color:#8a1c0c}#pdfStage .ex{background:#f4f8fc;border-left:3px solid #1d63c7;padding:5px 9px;margin-top:5px;font-size:13px}#pdfStage .jb{display:inline-block;padding:12px 18px;border-radius:12px;color:#fff;font-weight:700;font-size:15px;margin-right:10px}</style>`;
  const mk=h=>{const e=document.createElement('div');e.className='pb';e.innerHTML=h;st.appendChild(e);blocks.push(e);return e};
  st.innerHTML=css;
  const head=`<div style="display:flex;align-items:center;gap:14px;border-bottom:3px solid #0f2a4d;padding-bottom:10px"><img src="${esc(C.logo.replace('.svg','.png'))}" style="width:62px;height:62px;border-radius:50%"><div style="flex:1"><h1>UPSSSC ALERT</h1><div style="font-size:13px;color:#5f6b7a">UP Super TET 2026 — लाइव मॉक टेस्ट · xlstudy.in</div></div><div style="text-align:right;font-size:13px;color:#0f2a4d;font-weight:700">XL Study<br><span style="font-weight:400;color:#5f6b7a">xlstudy.in</span></div></div>`;
  if(d.name!=null){const per=perSec(d),sk=120-d.correct-d.wrong;
   mk(head+`<table style="margin-top:12px"><tr><th>उम्मीदवार का नाम</th><td>${esc(d.name)}</td><th>रोल नंबर</th><td>${esc(d.roll)}</td></tr><tr><th>ईमेल</th><td>${esc(d.email||'')}</td><th>परीक्षा तिथि</th><td>${d.started?ist(d.started,{day:'numeric',month:'long',year:'numeric'}):''}</td></tr><tr><th>कुल अंक</th><td><b>${d.score} / 360</b></td><th>परसेंटाइल</th><td><b>${d.percentile.toFixed(2)}</b></td></tr><tr><th>रैंक</th><td>${d.rank} / ${d.total}</td><th>लगा समय</th><td>${mins(d.used)}</td></tr><tr><th>सही / गलत / छोड़े</th><td>${d.correct} / ${d.wrong} / ${sk}</td><th>टॉपर / औसत</th><td>${d.topper} / ${d.avg}</td></tr></table>
   <table style="margin-top:10px"><tr><th>सेक्शन</th><th>प्रश्न</th><th>सही</th><th>गलत</th><th>अंक</th></tr>${per.map(p=>`<tr><td>${esc(p.n)}</td><td>${p.len}</td><td>${p.c}</td><td>${p.w}</td><td><b>${p.m}</b></td></tr>`).join('')}</table>`)}
  else mk(head+'<p style="font-size:14px;margin:10px 0 0">120 प्रश्न · 360 अंक · 120 मिनट · सही +3 · गलत −1</p>');
  const ans=d.ans||{};
  d.q.forEach((q,i)=>{const a=ans[i];mk(`<div class="qq"><b>प्रश्न ${i+1}.</b> ${q.p&&!(d.q[i-1]&&d.q[i-1].p===q.p)?'<div style="color:#5f6b7a;font-size:13px">'+esc(q.p)+'</div>':''}${esc(QT(q))}${QO(q).map((o,j)=>`<div class="o ${L[j]===d.key[i]?'ok':(L[j]===a?'bad':'')}">(${j+1}) ${esc(o)}${L[j]===d.key[i]?' ✓':(L[j]===a?' ✗ आपका उत्तर':'')}</div>`).join('')}${d.name!=null&&!a?'<div style="font-size:12.5px;color:#5f6b7a">आपने यह प्रश्न छोड़ा</div>':''}${d.exp&&d.exp[i]?`<div class="ex"><b>व्याख्या:</b> ${esc(d.exp[i])}</div>`:''}</div>`)});
  const jb=mk(`<div style="border:2px solid #0f2a4d;border-radius:16px;padding:16px;text-align:center"><div style="font-size:17px;font-weight:700;color:#0f2a4d;margin-bottom:10px">रोज़ाना क्विज, नोट्स और मॉक टेस्ट के लिए जुड़ें</div><span class="jb" id="pj1" style="background:#229ed9">टेलीग्राम चैनल ज्वाइन करें</span><span class="jb" id="pj2" style="background:#0f2a4d">टेलीग्राम ग्रुप ज्वाइन करें</span><div style="font-size:13px;color:#5f6b7a;margin-top:10px">आचार्य 2.0 Super TET बैच ₹199 · UP SUPER TET बुक ₹429 · xlstudy.in</div></div>`);
  await Promise.all([...st.querySelectorAll('img')].map(im=>im.complete?1:new Promise(r=>{im.onload=im.onerror=r})));
  const {jsPDF}=window.jspdf,pdf=new jsPDF({unit:'pt',format:'a4',compress:true}),PW=pdf.internal.pageSize.getWidth(),PH2=pdf.internal.pageSize.getHeight(),mg=22,sc=(PW-2*mg)/760,cap=(PH2-2*mg-14)/sc;
  const pages=[];let cur=[],hh=0;blocks.forEach(b=>{const h=b.offsetHeight+4;if(hh+h>cap&&cur.length){pages.push(cur);cur=[];hh=0}cur.push(b);hh+=h});if(cur.length)pages.push(cur);
  for(let n=0;n<pages.length;n++){M.textContent='PDF बन रही है… '+Math.round(n*100/pages.length)+'%';
   const pd=document.createElement('div');pd.style.cssText='width:760px;background:#fff';pages[n].forEach(b=>{b.style.marginBottom='4px';pd.appendChild(b)});st.appendChild(pd);
   const cv=await html2canvas(pd,{scale:1.3,backgroundColor:'#ffffff',useCORS:true,logging:false,ignoreElements:e=>e.classList&&(e.classList.contains('scr')||e.classList.contains('mdl'))});
   if(n)pdf.addPage();pdf.addImage(cv.toDataURL('image/jpeg',.72),'JPEG',mg,mg,PW-2*mg,pd.offsetHeight*sc);
   if(pages[n].includes(jb)){const pr=pd.getBoundingClientRect();[['pj1',C.tgc],['pj2',C.tgg]].forEach(([id,u])=>{const r=jb.querySelector('#'+id).getBoundingClientRect();pdf.link(mg+(r.left-pr.left)*sc,mg+(r.top-pr.top)*sc,r.width*sc,r.height*sc,{url:u})})}
   pd.remove();await new Promise(r=>setTimeout(r,0))}
  const np=pdf.internal.getNumberOfPages();
  for(let p=1;p<=np;p++){pdf.setPage(p);pdf.saveGraphicsState();pdf.setGState(new pdf.GState({opacity:.07}));pdf.setTextColor(15,42,77);pdf.setFont('helvetica','bold');pdf.setFontSize(64);pdf.text('UPSSSC ALERT',PW/2,PH2/2,{align:'center',angle:35});pdf.restoreGraphicsState();
   pdf.setFontSize(8.5);pdf.setTextColor(120);pdf.setFont('helvetica','normal');pdf.text('UPSSSC ALERT  x  XL Study  ·  xlstudy.in',mg,PH2-10);pdf.text(p+' / '+np,PW-mg,PH2-10,{align:'right'})}
  pdf.save('SuperTET-Mock-'+(d.name?String(d.name).replace(/[^A-Za-z0-9]+/g,'-').replace(/^-|-$/g,'')||'Result':'Paper')+'.pdf');st.innerHTML='';W.classList.remove('on')}
 catch(e){W.classList.remove('on');$('pdfStage').innerHTML='';modal('PDF नहीं बनी','<p style="margin:0">नेट जाँचकर दोबारा कोशिश करें।</p>')}}
function paper(){api('paper').then(j=>makePdf({q:j.q,key:j.key,exp:j.exp,name:null})).catch(e=>modal('सूचना','<p style="margin:0">'+esc((e&&e.message)||'उपलब्ध नहीं')+'</p>'))}
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&$('mf').children.length){close()}if(e.key==='Escape')side(0)});

/* ---------- admin ---------- */
function adm(){api('admin'+(AR?'?round='+AR:'')).then(d=>{off=d.now*1000-Date.now();const n=d.now,rows=d.rows;
 $('a-t').textContent=d.round.name;const ro=d.rounds.map(x=>`<option value="${x.id}"${x.id===d.round.id?' selected':''}>${esc(x.name)}</option>`).join('');if($('a-r').dataset.k!==ro){$('a-r').innerHTML=ro;$('a-r').dataset.k=ro}
 const live=rows.filter(x=>+x.started&&!+x.submitted&&n-x.last<120).length,st=rows.filter(x=>+x.started).length,dn=rows.filter(x=>+x.submitted&&+x.counted),av=dn.length?Math.round(dn.reduce((a,x)=>a+ +x.score,0)/dn.length):0,tp=dn.length?Math.max(...dn.map(x=>+x.score)):0;
 const uu=new Set(rows.map(x=>x.uid)).size;$('a-k').innerHTML=[[uu,'लॉग-इन करके पेज खोला'],[live,'अभी पेपर दे रहे'],[st,'पेपर शुरू किया'],[dn.length,'सबमिट किया'],[av+' / '+tp,'औसत / टॉप स्कोर']].map(x=>`<div><b>${x[0]}</b><span>${x[1]}</span></div>`).join('');
 const ago=t=>{t=n-t;return t<60?t+' से पहले':t<3600?Math.floor(t/60)+' मि पहले':Math.floor(t/3600)+' घं पहले'};
 $('a-tb').innerHTML='<thead><tr><th>नाम</th><th>ईमेल</th><th>प्रयास</th><th>स्थिति</th><th>डिवाइस</th><th>किए</th><th>स्कोर</th><th>शुरू</th><th>आखिरी बार</th><th>टैब</th><th></th></tr></thead>'+rows.map(x=>{const s=+x.submitted?'<span class="pill p-o">सबमिट</span>':+x.started?(n-x.last<120?'<span class="pill p-l">दे रहे</span>':'<span class="pill p-i">रुके हैं</span>'):'<span class="pill p-s">केवल लॉग-इन</span>';
  return `<tr style="${+x.submitted&&!+x.counted?'opacity:.55':''}"><td>${esc(x.name)}</td><td>${esc(x.email)}</td><td>${x.att}${+x.counted?' ★':''}</td><td>${s}</td><td>${esc(x.dev)}</td><td>${x.answered}</td><td><b>${+x.submitted?x.score:'—'}</b></td><td>${+x.started?tStr(+x.started):'—'}</td><td>${ago(+x.last)}</td><td>${x.tabs}</td><td><button class="b b-wh" style="padding:4px 10px;font-size:12px" data-rs="${x.id}">रीसेट</button></td></tr>`}).join('');
 window._rows=rows}).catch(()=>{})}

/* ---------- boot ---------- */
home();waitTick();
setInterval(()=>{if($('s-home').classList.contains('on'))home();if($('s-ins2').classList.contains('on'))waitTick()},1000);
if(C.admin){go('s-adm');ME.name='Admin';adm();setInterval(adm,10000);
 $('a-r').onchange=e=>{AR=e.target.value;adm()};
 $('a-tb').onclick=e=>{const b=e.target.closest('[data-rs]');if(b)modal('रीसेट करें?','<p style="margin:0">इस उम्मीदवार का प्रयास मिट जाएगा और वह दोबारा दे सकेगा।</p>',[['नहीं','b-wh',close],['हाँ, रीसेट','b-pr',()=>{close();api('reset',{id:+b.dataset.rs}).then(adm)}]])};
 $('a-csv').onclick=()=>{const rows=window._rows||[];const c='name,email,status,device,answered,correct,wrong,score,started,submitted,tabs\n'+rows.map(x=>[x.name,x.email,+x.submitted?'submitted':+x.started?'started':'login',x.dev,x.answered,x.correct,x.wrong,x.score,+x.started?new Date(x.started*1000).toLocaleString('en-IN',{timeZone:'Asia/Kolkata'}):'',+x.submitted?new Date(x.submitted*1000).toLocaleString('en-IN',{timeZone:'Asia/Kolkata'}):'',x.tabs].map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
  const a=document.createElement('a');a.href=URL.createObjectURL(new Blob(['﻿'+c],{type:'text/csv'}));a.download='super-tet-mock.csv';document.body.appendChild(a);a.click();a.remove()}}
else if(C.in){api('state').then(j=>{off=j.now*1000-Date.now();R=j.round;A=j.attempt;ME={name:j.name,roll:j.roll};names();home();
 if(+A.started&&!+A.submitted){try{const b=JSON.parse(localStorage.getItem(LK())||'null');if(b&&b.t>(+A.last||0)+2){A.ans=b.ans||A.ans;A.st=b.st||A.st;A.tq=b.tq||A.tq;A.cur=b.cur;A.tabs=Math.max(+A.tabs||0,+b.tabs||0);dirty=true}}catch(e){}
  modal('परीक्षा अधूरी है — Resume Test','<p style="margin:0 0 8px;line-height:1.7">आपकी परीक्षा पहले से चल रही है। आपके सभी उत्तर सुरक्षित हैं।</p><p style="margin:0;line-height:1.7">बचा समय: <b class="num">'+fmt(A.deadline-now())+'</b> · उत्तर दिए: <b>'+Object.keys(A.ans||{}).length+'</b> / 120</p><p style="margin:8px 0 0;font-size:13px;color:var(--mu)">ध्यान दें: घड़ी लगातार चल रही है।</p>',[['बाद में','b-wh',close],['Resume Test ›','b-pr',()=>{close();start();if(dirty)push().catch(()=>{})}]])}}).catch(()=>{$('c-h').textContent='लोड नहीं हुआ';$('c-p').textContent='पेज दोबारा खोलें।'})}
}
</script>
</body>
</html>
