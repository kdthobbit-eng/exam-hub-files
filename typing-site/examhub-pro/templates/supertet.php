<?php
/* Super TET live mock — TCS iON style computer based test. Unlisted page, noindex. */
if (!defined('ABSPATH')) exit;
header('X-Robots-Tag: noindex, nofollow', true);
nocache_headers();
$eh_q = @file_get_contents(EH_DIR . '/data/supertet-mock1.json');
if (!$eh_q) $eh_q = '[]';
$eh_u = is_user_logged_in() ? wp_get_current_user() : null;
$eh_name = $eh_u ? ($eh_u->display_name ?: $eh_u->user_login) : '';
$eh_mail = $eh_u ? $eh_u->user_email : '';
?><!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#2f5d8a">
<title>Super TET Mock 2026 — Online Examination</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap">
<style>
:root{--hd:#2f5d8a;--sec:#4e85c5;--pal:#e5f1fb;--ln:#c9d6e3;--tx:#222;--mu:#5d6773;--bl:#0c7cd5;--gr:#3aa54a;--rd:#e2412d;--pu:#6f42c1;--org:#f0a30a}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0;height:100%}
body{font-family:Roboto,'Noto Sans Devanagari',Arial,sans-serif;color:var(--tx);background:#fff;font-size:14px}
button{font-family:inherit;cursor:pointer}
.scr{display:none}.scr.on{display:block}
.hide{display:none!important}

/* header (TCS) */
.hd{background:#fff;border-bottom:3px solid var(--hd);display:flex;align-items:center;justify-content:space-between;padding:6px 14px;gap:10px}
.brand{display:flex;align-items:center;gap:10px;min-width:0}
.brand i{width:38px;height:38px;border-radius:6px;background:var(--hd);color:#fff;font-style:normal;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:15px;flex:none}
.brand b{display:block;font-size:16px;color:var(--hd);white-space:nowrap}
.brand span{display:block;font-size:11.5px;color:var(--mu)}
.hdr{display:flex;align-items:center;gap:12px}
.cinfo{font-size:12.5px;line-height:1.5;text-align:left}
.cinfo b{color:#000}
.photo{width:44px;height:50px;border:1px solid var(--ln);background:#eef3f8;display:flex;align-items:flex-end;justify-content:center;overflow:hidden;flex:none}
.photo svg{width:40px;height:40px;fill:#9fb1c4}
.timebox{border:1px solid var(--ln);background:#f6f9fc;padding:4px 10px;font-size:12px;color:var(--mu);text-align:center}
.timebox b{display:block;font-size:19px;color:#000;font-variant-numeric:tabular-nums;letter-spacing:.5px}
.timebox.low b{color:var(--rd)}

/* login */
.lgwrap{min-height:calc(100vh - 60px);background:#eef3f8;display:flex;align-items:flex-start;justify-content:center;padding:30px 12px}
.lgbox{background:#fff;border:1px solid var(--ln);width:100%;max-width:880px;display:grid;grid-template-columns:1fr 1fr}
.lgl{background:var(--hd);color:#fff;padding:26px 24px}
.lgl h2{margin:0 0 4px;font-size:20px}.lgl p{margin:0 0 16px;opacity:.85;font-size:13px}
.lgl table{width:100%;border-collapse:collapse;font-size:13.5px}.lgl td{padding:7px 0;border-bottom:1px solid rgba(255,255,255,.15)}.lgl td:last-child{text-align:right;font-weight:700}
.lgr{padding:26px 24px}
.lgr h3{margin:0 0 14px;color:var(--hd);font-size:17px}
.fld{margin-bottom:12px}.fld label{display:block;font-size:12.5px;color:var(--mu);margin-bottom:4px}
.fld input{width:100%;padding:11px 12px;border:1px solid var(--ln);border-radius:3px;font-size:16px;font-family:inherit}
.fld input:focus{outline:2px solid #9cc3ec;border-color:var(--bl)}
.err{color:var(--rd);font-size:12.5px;min-height:18px}
.b{border:1px solid transparent;border-radius:3px;padding:10px 18px;font-size:14px;font-weight:500;line-height:1.2}
.b-bl{background:var(--bl);color:#fff}.b-bl:hover{background:#0a6ab6}
.b-wh{background:#fff;border-color:#adb8c4;color:#222}.b-wh:hover{background:#f2f5f8}
.b-gr{background:var(--gr);color:#fff}
.b-full{width:100%;padding:12px;font-size:15px}

/* instructions */
.insbar{background:var(--sec);color:#fff;padding:9px 16px;font-weight:500;display:flex;justify-content:space-between}
.ins{max-width:1100px;margin:0 auto;padding:16px 20px 120px;line-height:1.75;font-size:14.5px}
.ins h3{text-align:center;margin:4px 0 12px;font-size:16px}
.ins ol{padding-left:22px}.ins li{margin-bottom:6px}
.lg{display:flex;align-items:center;gap:10px;margin:6px 0}
.insft{position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid var(--ln);padding:10px 16px calc(10px + env(safe-area-inset-bottom));display:flex;justify-content:space-between;gap:10px;align-items:center;z-index:5}
.agree{display:flex;gap:10px;align-items:flex-start;background:#f6f9fc;border:1px solid var(--ln);padding:12px;margin-top:14px;font-size:14px}
.agree input{width:20px;height:20px;flex:none;margin-top:2px}

/* palette shapes (TCS) */
.q{min-width:36px;height:32px;padding:0 4px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:500;border:1px solid #9aa6b2;background:#fff;color:#222;position:relative;border-radius:3px}
.q.na{background:var(--rd);border-color:var(--rd);color:#fff;border-radius:0 0 14px 14px}
.q.an{background:var(--gr);border-color:var(--gr);color:#fff;border-radius:14px 14px 0 0}
.q.mr,.q.am{background:var(--pu);border-color:var(--pu);color:#fff;border-radius:50%;min-width:34px;width:34px;height:34px}
.q.am:after{content:"";position:absolute;right:-3px;bottom:-2px;width:12px;height:12px;border-radius:50%;background:var(--gr) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M3 6.2l2 2 4-4.4' fill='none' stroke='%23fff' stroke-width='1.6'/%3E%3C/svg%3E") center/12px;border:1px solid #fff}

/* exam */
#s-exam{height:100vh;height:100dvh;flex-direction:column;overflow:hidden}
#s-exam.on{display:flex}
.secbar{display:flex;align-items:stretch;background:#fff;border-bottom:1px solid var(--ln)}
.secbar .lbl{background:#f1f4f7;padding:0 12px;display:flex;align-items:center;font-size:12.5px;color:var(--mu);border-right:1px solid var(--ln);flex:none}
.secs{display:flex;overflow-x:auto;scrollbar-width:none;flex:1}
.secs::-webkit-scrollbar{display:none}
.secs button{flex:none;border:0;border-right:1px solid var(--ln);background:#fff;padding:9px 14px;font-size:13px;color:#1d3f66;white-space:nowrap;position:relative}
.secs button.on{background:var(--sec);color:#fff}
.secs button small{display:inline-block;margin-left:6px;font-size:11px;background:#eaf1f9;color:#1d3f66;border-radius:8px;padding:0 6px}
.secs button.on small{background:rgba(255,255,255,.25);color:#fff}
.body{flex:1;display:flex;min-height:0}
.main{flex:1;display:flex;flex-direction:column;min-width:0}
.qbar{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:7px 14px;border-bottom:1px solid var(--ln);background:#fafcfe;flex-wrap:wrap}
.qbar .no{font-weight:700;font-size:15px}
.qbar .r{display:flex;gap:12px;align-items:center;font-size:12.5px;color:var(--mu)}
.qbar select{font-size:12.5px;padding:3px 6px;border:1px solid var(--ln);font-family:inherit}
.pos{color:var(--gr);font-weight:700}.neg{color:var(--rd);font-weight:700}
.qa{flex:1;overflow:auto;padding:14px 18px 24px;-webkit-overflow-scrolling:touch}
.pass{border:1px solid var(--ln);background:#f8fafc;padding:10px 12px;margin-bottom:12px;line-height:1.75;font-size:14.5px;max-height:38vh;overflow:auto}
.pass b{display:block;font-size:12.5px;color:var(--mu);margin-bottom:4px}
.qt{font-size:16px;line-height:1.8;margin-bottom:12px;font-family:'Noto Sans Devanagari',Roboto,sans-serif}
.op{display:flex;gap:12px;align-items:flex-start;padding:9px 10px;margin-bottom:4px;line-height:1.65;cursor:pointer;font-size:15.5px;border:1px solid transparent;border-radius:3px;font-family:'Noto Sans Devanagari',Roboto,sans-serif}
.op:hover{background:#f3f7fb}
.op.sel{background:#e9f3fd;border-color:#bcd8f3}
.op input{width:18px;height:18px;margin-top:5px;flex:none;accent-color:var(--bl)}
.acts{display:flex;justify-content:space-between;gap:8px;padding:9px 14px;border-top:1px solid var(--ln);background:#fff}
.acts .l,.acts .r{display:flex;gap:8px}
.side{width:300px;flex:none;border-left:1px solid var(--ln);background:var(--pal);display:flex;flex-direction:column;min-height:0}
.side .cand{display:flex;gap:10px;align-items:center;padding:10px 12px;background:#fff;border-bottom:1px solid var(--ln)}
.side .cand b{display:block;font-size:14px}
.side .cand span{font-size:12px;color:var(--mu)}
.lgd{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:10px 12px;font-size:12px;background:#fff;border-bottom:1px solid var(--ln)}
.lgd div{display:flex;align-items:center;gap:6px;line-height:1.25}
.lgd .w{grid-column:1/3}
.lgd .q{min-width:30px;height:28px;font-size:12px}.lgd .q.mr,.lgd .q.am{min-width:30px;width:30px;height:30px}
.pview{background:var(--sec);color:#fff;padding:7px 12px;font-size:13px}
.pview b{font-weight:700}
.palg{flex:1;overflow:auto;padding:12px;display:grid;grid-template-columns:repeat(5,1fr);gap:10px 8px;align-content:start;justify-items:center}
.palg .q.cur{box-shadow:0 0 0 2px #fff,0 0 0 4px var(--org)}
.side .ft{padding:10px 12px;background:#fff;border-top:1px solid var(--ln);display:grid;grid-template-columns:1fr 1fr;gap:8px}
.side .ft .b-bl{grid-column:1/3;padding:11px;font-size:15px}
.mob{display:none}
.shade{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:20}.shade.on{display:block}

/* modal */
.mdl{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:40;padding:12px}
.mdl.on{display:flex}
.mdl .bx{background:#fff;width:100%;max-width:760px;max-height:92vh;display:flex;flex-direction:column;border-radius:4px;overflow:hidden}
.mdl .bx>h3{margin:0;background:var(--hd);color:#fff;padding:11px 16px;font-size:15px;font-weight:500;display:flex;justify-content:space-between;align-items:center}
.mdl .bx>h3 button{background:none;border:0;color:#fff;font-size:22px;line-height:1;padding:0 4px}
.mdl .in{padding:14px 16px;overflow:auto}
.mdl .ftr{padding:10px 16px;border-top:1px solid var(--ln);display:flex;justify-content:flex-end;gap:8px}
table.t{width:100%;border-collapse:collapse;font-size:13px}
table.t th,table.t td{border:1px solid var(--ln);padding:7px 8px;text-align:center}
table.t th{background:#eef3f8;font-weight:500}
table.t td:first-child,table.t th:first-child{text-align:left}
.tw{overflow-x:auto}

/* result */
.rwrap{max-width:1000px;margin:0 auto;padding:16px 12px 40px}
.rhead{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.rhead h2{margin:0;color:var(--hd);font-size:20px}
.kp{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:14px}
.kp div{border:1px solid var(--ln);padding:12px 8px;text-align:center;border-radius:4px}
.kp b{display:block;font-size:22px}.kp span{font-size:12px;color:var(--mu)}
.filt{display:flex;gap:6px;flex-wrap:wrap;margin:18px 0 10px}
.filt button{border:1px solid var(--ln);background:#fff;padding:6px 12px;border-radius:16px;font-size:13px}
.filt button.on{background:var(--hd);color:#fff;border-color:var(--hd)}
.sol{border:1px solid var(--ln);border-radius:4px;padding:12px 14px;margin-bottom:10px;line-height:1.7;font-family:'Noto Sans Devanagari',Roboto,sans-serif}
.sol .h{display:flex;justify-content:space-between;gap:8px;font-size:13px;color:var(--mu);margin-bottom:4px}
.sol .o{padding:3px 8px;border-radius:3px;margin:2px 0}
.sol .ok{background:#e3f5e6;color:#14532d;font-weight:600}
.sol .bad{background:#fde6e2;color:#8a1c0c}
.sol .ex{background:#f4f8fc;border-left:3px solid var(--sec);padding:7px 10px;margin-top:6px;font-size:14px}
.tg{font-size:12px;font-weight:700;padding:1px 8px;border-radius:10px}
.tg.c{background:#dcfce7;color:#166534}.tg.w{background:#fee2e2;color:#991b1b}.tg.s{background:#eef2f7;color:#475569}

#printArea{display:none}
@media print{.scr,.mdl,.shade{display:none!important}#printArea{display:block;font-family:'Noto Sans Devanagari',Arial,sans-serif;font-size:11.5px;line-height:1.5}
 #printArea h1{text-align:center;font-size:16px;margin:0 0 2px}#printArea .pq{break-inside:avoid;margin-bottom:7px}
 #printArea .cols{column-count:2;column-gap:20px}#printArea .key{display:grid;grid-template-columns:repeat(10,1fr);gap:3px}}

/* ---------- tablet ---------- */
@media(max-width:1024px){.side{width:260px}.palg{grid-template-columns:repeat(4,1fr)}}
/* ---------- mobile ---------- */
@media(max-width:760px){
 body{font-size:14px}
 .hd{padding:6px 10px}.brand i{width:32px;height:32px;font-size:13px}.brand b{font-size:14px}.brand span,.cinfo,.hd .photo{display:none}
 .timebox{padding:3px 8px}.timebox b{font-size:17px}.timebox span{font-size:10.5px}
 .lgbox{grid-template-columns:1fr}.lgwrap{padding:12px 10px}.lgl{padding:18px 16px}.lgr{padding:18px 16px}
 .ins{padding:12px 14px 140px;font-size:14px}
 .insft{flex-wrap:wrap}.insft .b{flex:1}
 .secbar .lbl{display:none}
 .secs button{padding:10px 12px}
 .qbar{padding:7px 12px}.qbar .r .lang{display:none}
 .qa{padding:12px 12px 16px}
 .qt{font-size:16px}.op{font-size:15.5px;padding:11px 10px;border:1px solid #e1e8ef;margin-bottom:8px}
 .acts{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:8px 10px calc(8px + env(safe-area-inset-bottom))}
 .acts .l,.acts .r{display:contents}
 .acts .b{padding:12px 6px;font-size:13.5px;width:100%}
 .mob{display:inline-flex}
 .side{position:fixed;left:0;right:0;bottom:0;top:auto;width:auto;height:82vh;height:82dvh;border:0;border-radius:12px 12px 0 0;z-index:30;transform:translateY(105%);transition:transform .25s ease;box-shadow:0 -8px 30px rgba(0,0,0,.25);overflow:hidden}
 .side.open{transform:none}
 .side .grab{display:flex}
 .palg{grid-template-columns:repeat(6,1fr)}
 table.t{font-size:12px}table.t th,table.t td{padding:6px 4px}
 .kp{grid-template-columns:repeat(2,minmax(0,1fr))}.kp div:first-child{grid-column:1/3}
}
.grab{display:none;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border-bottom:1px solid var(--ln);font-weight:700}
.grab button{border:0;background:#eef3f8;border-radius:50%;width:32px;height:32px;font-size:20px;line-height:1}
.pbtn{align-items:center;gap:6px;border:1px solid var(--ln);background:#fff;border-radius:3px;padding:6px 9px;font-size:12.5px;color:#1d3f66}
.pbtn svg{width:16px;height:16px;fill:#1d3f66}
@media(prefers-reduced-motion:reduce){.side{transition:none}}
</style>
</head>
<body>

<!-- ===== login ===== -->
<section class="scr on" id="s-login">
 <div class="hd"><div class="brand"><i>XL</i><div><b>XL Study · Online Examination</b><span>Computer Based Test</span></div></div><div class="timebox"><span>Exam starts</span><b>10:00 AM</b></div></div>
 <div class="lgwrap"><div class="lgbox">
  <div class="lgl"><h2>UP Super TET 2026</h2><p>लाइव मॉक टेस्ट — सहायक अध्यापक (प्राथमिक)</p>
   <table><tr><td>कुल प्रश्न</td><td>120</td></tr><tr><td>पूर्णांक</td><td>360</td></tr><tr><td>समय</td><td>120 मिनट</td></tr><tr><td>सही उत्तर</td><td>+3</td></tr><tr><td>गलत उत्तर</td><td>−1</td></tr><tr><td>सेक्शन</td><td>10</td></tr></table></div>
  <div class="lgr"><h3>Candidate Login</h3>
   <div class="fld"><label>उम्मीदवार का नाम</label><input id="lg-name" autocomplete="name" value="<?php echo esc_attr($eh_name); ?>" placeholder="अपना पूरा नाम"></div>
   <div class="fld"><label>ईमेल / मोबाइल</label><input id="lg-mail" value="<?php echo esc_attr($eh_mail); ?>" placeholder="ईमेल या मोबाइल नंबर"></div>
   <div class="err" id="lg-err"></div>
   <button class="b b-bl b-full" id="lg-go">Sign In</button>
   <p style="font-size:12px;color:var(--mu);margin:12px 0 0;line-height:1.6">परीक्षा शुरू होने के बाद पेज बंद हो जाए तो दोबारा खोलें — उत्तर और बचा समय वहीं से मिलेगा।</p>
  </div>
 </div></div>
</section>

<!-- ===== instructions 1 ===== -->
<section class="scr" id="s-ins1">
 <div class="hd"><div class="brand"><i>XL</i><div><b>XL Study · Online Examination</b><span>UP Super TET 2026 Mock</span></div></div><div class="hdr"><div class="cinfo">Candidate: <b class="cn"></b></div><div class="photo"><svg viewBox="0 0 40 40"><circle cx="20" cy="14" r="8"/><path d="M4 40c0-9 7-15 16-15s16 6 16 15z"/></svg></div></div></div>
 <div class="insbar"><span>Instructions</span><span>1 / 2</span></div>
 <div class="ins"><h3>कृपया निम्नलिखित निर्देशों को ध्यानपूर्वक पढ़ें</h3>
 <b>सामान्य निर्देश:</b><ol>
  <li>परीक्षा की कुल अवधि <b>120 मिनट</b> है। सर्वर पर लगी घड़ी स्क्रीन के ऊपर दाएँ कोने में बचा हुआ समय दिखाएगी। समय समाप्त होने पर परीक्षा अपने आप सबमिट हो जाएगी, आपको सबमिट करने की आवश्यकता नहीं होगी।</li>
  <li>प्रश्न-पत्र में <b>120 प्रश्न</b> हैं। प्रत्येक सही उत्तर पर <b>3 अंक</b> मिलेंगे और प्रत्येक गलत उत्तर पर <b>1 अंक</b> काटा जाएगा।</li>
  <li>स्क्रीन के दाईं ओर दिया गया प्रश्न-पैलेट (Question Palette) प्रत्येक प्रश्न की स्थिति निम्न चिह्नों से दिखाएगा:
   <div class="lg"><span class="q">1</span> आपने यह प्रश्न अभी तक नहीं देखा है।</div>
   <div class="lg"><span class="q na">2</span> आपने इस प्रश्न का उत्तर नहीं दिया है।</div>
   <div class="lg"><span class="q an">3</span> आपने इस प्रश्न का उत्तर दे दिया है।</div>
   <div class="lg"><span class="q mr">4</span> आपने उत्तर नहीं दिया है, पर प्रश्न को पुनर्विचार हेतु चिह्नित किया है।</div>
   <div class="lg"><span class="q am">5</span> उत्तर दिया और पुनर्विचार हेतु चिह्नित किया — इसका मूल्यांकन किया जाएगा।</div></li>
  <li>मोबाइल पर प्रश्न-पैलेट <b>"Palette"</b> बटन दबाने पर नीचे से खुलेगा।</li>
 </ol>
 <b>प्रश्न पर जाना:</b><ol start="5">
  <li>पैलेट में प्रश्न संख्या पर क्लिक करके सीधे उस प्रश्न पर जाएँ। ऐसा करने से वर्तमान प्रश्न का चुना गया उत्तर सेव नहीं होगा।</li>
  <li><b>Save &amp; Next</b> से उत्तर सेव होगा और अगला प्रश्न खुलेगा।</li>
  <li><b>Mark for Review &amp; Next</b> से उत्तर (यदि चुना हो) सेव होगा, प्रश्न चिह्नित होगा और अगला प्रश्न खुलेगा।</li>
 </ol></div>
 <div class="insft"><span></span><button class="b b-bl" data-go="s-ins2">Next ›</button></div>
</section>

<!-- ===== instructions 2 ===== -->
<section class="scr" id="s-ins2">
 <div class="hd"><div class="brand"><i>XL</i><div><b>XL Study · Online Examination</b><span>UP Super TET 2026 Mock</span></div></div><div class="hdr"><div class="cinfo">Candidate: <b class="cn"></b></div><div class="photo"><svg viewBox="0 0 40 40"><circle cx="20" cy="14" r="8"/><path d="M4 40c0-9 7-15 16-15s16 6 16 15z"/></svg></div></div></div>
 <div class="insbar"><span>Other Important Instructions</span><span>2 / 2</span></div>
 <div class="ins">
  <table class="t" style="max-width:640px;margin:0 auto 16px"><tr><th>क्र.</th><th>सेक्शन</th><th>प्रश्न</th><th>अंक</th></tr><tbody id="ins-tb"></tbody></table>
  <ol>
   <li>प्रश्न-पत्र में 10 सेक्शन हैं। ऊपर सेक्शन के नाम पर क्लिक करके किसी भी सेक्शन में कभी भी जा सकते हैं।</li>
   <li>उत्तर बदलने के लिए दूसरा विकल्प चुनें और Save &amp; Next दबाएँ। उत्तर हटाने के लिए <b>Clear Response</b> दबाएँ।</li>
   <li>परीक्षा के दौरान दूसरी टैब/ऐप खोलना वर्जित है। ऐसा करने पर चेतावनी आएगी और गिनती दर्ज होगी।</li>
   <li>प्रश्न हिंदी में हैं; आवश्यक शब्द कोष्ठक में अंग्रेज़ी में दिए गए हैं। अंग्रेज़ी भाषा के प्रश्न अंग्रेज़ी में हैं।</li>
  </ol>
  <label style="display:block;margin-top:10px">डिफ़ॉल्ट भाषा: <select style="padding:6px 10px;font-size:14px"><option>हिंदी</option></select></label>
  <label class="agree"><input type="checkbox" id="agree"><span>मैंने सभी निर्देश पढ़ और समझ लिए हैं। मैं घोषणा करता/करती हूँ कि परीक्षा में किसी भी अनुचित साधन का प्रयोग नहीं करूँगा/करूँगी।</span></label>
  <div class="err" id="ag-err"></div>
 </div>
 <div class="insft"><button class="b b-wh" data-go="s-ins1">‹ Previous</button><button class="b b-bl" id="begin">I am ready to begin</button></div>
</section>

<!-- ===== exam ===== -->
<section class="scr" id="s-exam">
 <div class="hd"><div class="brand"><i>XL</i><div><b>UP Super TET 2026</b><span>Live Mock · XL Study</span></div></div>
  <div class="hdr"><div class="cinfo">Candidate: <b class="cn"></b><br>Subject: <b>Super TET (Primary)</b></div><div class="photo"><svg viewBox="0 0 40 40"><circle cx="20" cy="14" r="8"/><path d="M4 40c0-9 7-15 16-15s16 6 16 15z"/></svg></div>
  <div class="timebox" id="tbox"><span>Time Left</span><b id="tm">2:00:00</b></div></div></div>
 <div class="secbar"><div class="lbl">Sections</div><div class="secs" id="secs"></div></div>
 <div class="body">
  <div class="main">
   <div class="qbar"><span class="no" id="qno">Question No. 1</span>
    <span class="r"><span class="lang">View in <select><option>हिंदी</option></select></span><span>Marks <span class="pos">+3</span> <span class="neg">−1</span></span>
    <button class="pbtn mob" id="popen"><svg viewBox="0 0 16 16"><path d="M1 1h4v4H1zm5 0h4v4H6zm5 0h4v4h-4zM1 6h4v4H1zm5 0h4v4H6zm5 0h4v4h-4zM1 11h4v4H1zm5 0h4v4H6zm5 0h4v4h-4z"/></svg>Palette</button></span></div>
   <div class="qa" id="qa"></div>
   <div class="acts"><div class="l"><button class="b b-wh" id="bmark">Mark for Review &amp; Next</button><button class="b b-wh" id="bclr">Clear Response</button></div>
    <div class="r"><button class="b b-wh" id="bback">‹ Back</button><button class="b b-bl" id="bsave">Save &amp; Next</button></div></div>
  </div>
  <aside class="side" id="side">
   <div class="grab"><span>Question Palette</span><button id="pclose" aria-label="बंद करें">×</button></div>
   <div class="cand"><div class="photo"><svg viewBox="0 0 40 40"><circle cx="20" cy="14" r="8"/><path d="M4 40c0-9 7-15 16-15s16 6 16 15z"/></svg></div><div><b class="cn"></b><span>Super TET Mock 2026</span></div></div>
   <div class="lgd" id="lgd"></div>
   <div class="pview">You are viewing <b id="pvs"></b> section<br><span style="opacity:.85">Question Palette:</span></div>
   <div class="palg" id="palg"></div>
   <div class="ft"><button class="b b-wh" id="bqp">Question Paper</button><button class="b b-wh" id="bins">Instructions</button><button class="b b-bl" id="bsub">Submit</button></div>
  </aside>
 </div>
 <div class="shade" id="shade"></div>
</section>

<!-- ===== result ===== -->
<section class="scr" id="s-res">
 <div class="hd"><div class="brand"><i>XL</i><div><b>XL Study · Result</b><span>UP Super TET 2026 Mock</span></div></div><div class="hdr"><div class="cinfo">Candidate: <b class="cn"></b></div></div></div>
 <div class="rwrap" id="resw"></div>
</section>

<div class="mdl" id="mdl"><div class="bx"><h3><span id="mt"></span><button id="mx" aria-label="बंद करें">×</button></h3><div class="in" id="mb"></div><div class="ftr" id="mf"></div></div></div>
<div id="printArea"></div>

<script>
{
const Q = <?php echo $eh_q; ?>;
const r=(a,b)=>Array.from({length:b-a+1},(_,i)=>a-1+i);
const SECS=[["सामान्य ज्ञान एवं समसामयिकी",r(1,25)],["तार्किक ज्ञान",r(26,30)],["हिंदी, संस्कृत एवं अंग्रेजी",r(31,60)],["विज्ञान",r(61,68)],["गणित",r(69,84)],["पर्यावरण एवं सामाजिक अध्ययन",r(85,92)],["शिक्षण कौशल",r(93,100)],["बाल मनोविज्ञान",r(101,108)],["सूचना तकनीक (ICT)",r(109,112).concat(r(117,120))],["जीवन कौशल / प्रबंधन",r(113,116)]].map(x=>({n:x[0],q:x[1]}));
const DUR=120*60, KEY='eh_stet_mock1';
let S={name:'',mail:'',ans:{},st:{},cur:0,end:0,tabs:0,done:false};
let sel=null,T=null;
const $=id=>document.getElementById(id);
const esc=s=>String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const L=['A','B','C','D'];
function load(){try{const x=JSON.parse(localStorage.getItem(KEY)||'null');if(x&&x.end)S=x}catch(e){}}
function save(){try{localStorage.setItem(KEY,JSON.stringify(S))}catch(e){}}
function go(id){document.querySelectorAll('.scr').forEach(s=>s.classList.toggle('on',s.id===id));window.scrollTo(0,0)}
function names(){document.querySelectorAll('.cn').forEach(e=>e.textContent=S.name)}
function modal(t,body,btns){$('mt').textContent=t;$('mb').innerHTML=body;$('mf').innerHTML='';(btns||[['बंद करें','b-bl',close]]).forEach(b=>{const e=document.createElement('button');e.className='b '+b[1];e.textContent=b[0];e.onclick=b[2];$('mf').appendChild(e)});$('mdl').classList.add('on')}
function close(){$('mdl').classList.remove('on')}
$('mx').onclick=close;
document.querySelectorAll('[data-go]').forEach(b=>b.onclick=()=>go(b.dataset.go));
$('ins-tb').innerHTML=SECS.map((s,i)=>`<tr><td>${i+1}</td><td style="text-align:left">${esc(s.n)}</td><td>${s.q.length}</td><td>${s.q.length*3}</td></tr>`).join('')+'<tr><th></th><th style="text-align:left">कुल</th><th>120</th><th>360</th></tr>';

$('lg-go').onclick=()=>{const n=$('lg-name').value.trim(),m=$('lg-mail').value.trim();
 if(n.length<2){$('lg-err').textContent='कृपया अपना नाम लिखें';return}
 if(m.length<5){$('lg-err').textContent='कृपया ईमेल या मोबाइल नंबर लिखें';return}
 S.name=n;S.mail=m;names();go('s-ins1')};
$('begin').onclick=()=>{if(!$('agree').checked){$('ag-err').textContent='आगे बढ़ने के लिए घोषणा पर ✓ लगाएँ';return}
 S.end=Date.now()+DUR*1000;save();start()};

const secOf=i=>SECS.findIndex(s=>s.q.includes(i));
const stOf=i=>S.st[i]||'nv';
const fmt=s=>{const h=Math.floor(s/3600),m=Math.floor(s%3600/60),x=s%60;return h+':'+String(m).padStart(2,'0')+':'+String(x).padStart(2,'0')};
function left(){return Math.max(0,Math.round((S.end-Date.now())/1000))}
function tick(){const l=left();$('tm').textContent=fmt(l);$('tbox').classList.toggle('low',l<=300);if(l<=0&&!S.done){finish(true)}}
function start(){names();go('s-exam');show(S.cur||0);tick();T=setInterval(tick,1000);
 try{if(window.innerWidth<=760&&document.documentElement.requestFullscreen)document.documentElement.requestFullscreen().catch(()=>{})}catch(e){}}
document.addEventListener('visibilitychange',()=>{if(document.hidden&&S.end&&!S.done){S.tabs++;save();setTimeout(()=>modal('चेतावनी','<p style="line-height:1.7;margin:0">परीक्षा के दौरान दूसरी टैब या ऐप खोलना वर्जित है। यह गिनती दर्ज कर ली गई है। (कुल: <b>'+S.tabs+'</b>)</p>'),300)}});

function secs(){const k=secOf(S.cur);$('secs').innerHTML=SECS.map((s,i)=>{const a=s.q.filter(j=>/an|am/.test(stOf(j))).length;return `<button class="${i===k?'on':''}" data-s="${i}">${esc(s.n)}<small>${a}/${s.q.length}</small></button>`}).join('');
 const on=$('secs').querySelector('.on');if(on)on.scrollIntoView({inline:'center',block:'nearest'})}
$('secs').onclick=e=>{const b=e.target.closest('[data-s]');if(b)show(SECS[+b.dataset.s].q[0])};
function show(i){S.cur=i;if(stOf(i)==='nv')S.st[i]='na';sel=S.ans[i]||null;const q=Q[i];
 $('qno').textContent='Question No. '+(i+1);
 let h='';if(q.p)h+='<div class="pass"><b>गद्यांश / Passage</b>'+esc(q.p.replace(/^(निर्देश|Direction)[^:]*:\s*/,''))+'</div>';
 h+='<div class="qt">'+esc(q.q)+'</div>';
 h+=q.o.map((o,k)=>`<label class="op${sel===L[k]?' sel':''}"><input type="radio" name="op" value="${L[k]}"${sel===L[k]?' checked':''}><span>${k+1}. ${esc(o)}</span></label>`).join('');
 $('qa').innerHTML=h;$('qa').scrollTop=0;secs();pal();save()}
$('qa').onchange=e=>{if(e.target.name==='op'){sel=e.target.value;document.querySelectorAll('.op').forEach(o=>o.classList.toggle('sel',o.querySelector('input').checked))}};
function next(){const k=secOf(S.cur),s=SECS[k].q,p=s.indexOf(S.cur);if(p<s.length-1)show(s[p+1]);else if(k<SECS.length-1)show(SECS[k+1].q[0]);else{secs();pal();save()}}
$('bsave').onclick=()=>{const i=S.cur;if(sel){S.ans[i]=sel;S.st[i]='an'}else{delete S.ans[i];S.st[i]='na'}next()};
$('bmark').onclick=()=>{const i=S.cur;if(sel){S.ans[i]=sel;S.st[i]='am'}else{delete S.ans[i];S.st[i]='mr'}next()};
$('bclr').onclick=()=>{const i=S.cur;sel=null;delete S.ans[i];S.st[i]='na';show(i)};
$('bback').onclick=()=>{const k=secOf(S.cur),s=SECS[k].q,p=s.indexOf(S.cur);if(p>0)show(s[p-1]);else if(k>0){const t=SECS[k-1].q;show(t[t.length-1])}};
function cnt(list){const c={an:0,na:0,nv:0,mr:0,am:0};list.forEach(i=>c[stOf(i)]++);return c}
function pal(){const k=secOf(S.cur),s=SECS[k],c=cnt(s.q);
 $('lgd').innerHTML=`<div><span class="q an">${c.an}</span>Answered</div><div><span class="q na">${c.na}</span>Not Answered</div><div><span class="q">${c.nv}</span>Not Visited</div><div><span class="q mr">${c.mr}</span>Marked for Review</div><div class="w"><span class="q am">${c.am}</span>Answered &amp; Marked for Review (will be considered for evaluation)</div>`;
 $('pvs').textContent=s.n;
 $('palg').innerHTML=s.q.map(i=>`<button class="q ${stOf(i)}${i===S.cur?' cur':''}" data-q="${i}">${i+1}</button>`).join('')}
$('palg').onclick=e=>{const b=e.target.closest('[data-q]');if(b){show(+b.dataset.q);side(0)}};
function side(o){$('side').classList.toggle('open',!!o);$('shade').classList.toggle('on',!!o)}
$('popen').onclick=()=>side(1);$('pclose').onclick=()=>side(0);$('shade').onclick=()=>side(0);
$('bins').onclick=()=>{side(0);modal('Instructions','<ul style="line-height:1.9;margin:0;padding-left:20px"><li>120 प्रश्न · 120 मिनट · 360 अंक</li><li>सही उत्तर +3, गलत उत्तर −1, छोड़ा 0</li><li>उत्तर सेव करने के लिए Save &amp; Next दबाएँ</li><li>किसी भी सेक्शन में कभी भी जा सकते हैं</li><li>समय समाप्त होने पर परीक्षा अपने आप सबमिट होगी</li></ul>')};
$('bqp').onclick=()=>{side(0);modal('Question Paper',Q.map((q,i)=>`<p style="margin:0 0 10px;line-height:1.65;font-family:'Noto Sans Devanagari'"><b>${i+1}.</b> ${esc(q.q)}</p>`).join(''))};
$('bsub').onclick=()=>{side(0);const rows=SECS.map(s=>{const c=cnt(s.q);return `<tr><td>${esc(s.n)}</td><td>${s.q.length}</td><td>${c.an+c.am}</td><td>${c.na}</td><td>${c.mr+c.am}</td><td>${c.nv}</td></tr>`}).join('');
 modal('Exam Summary',`<div class="tw"><table class="t"><tr><th>Section</th><th>No. of Questions</th><th>Answered</th><th>Not Answered</th><th>Marked for Review</th><th>Not Visited</th></tr>${rows}</table></div><p style="margin:14px 0 0;font-weight:500;line-height:1.6">क्या आप परीक्षा सबमिट करना चाहते हैं? सबमिट के बाद उत्तर बदले नहीं जा सकेंगे।</p>`,[['No','b-wh',close],['Yes, Submit','b-bl',()=>{close();finish(false)}]])};

function finish(auto){S.done=true;clearInterval(T);save();
 try{if(document.fullscreenElement)document.exitFullscreen()}catch(e){}
 let c=0,w=0;const per=SECS.map(s=>{let sc=0,sw=0;s.q.forEach(i=>{const a=S.ans[i];if(!a)return;a===Q[i].a?sc++:sw++});c+=sc;w+=sw;return [s.n,s.q.length,sc+sw,sc,sw,sc*3-sw]});
 const sk=120-c-w,tot=c*3-w;
 const acc=c+w?Math.round(c*100/(c+w)):0;
 let h=`<div class="rhead"><div><h2>Score Card</h2><div style="color:var(--mu);font-size:13px;margin-top:3px">${esc(S.name)} · UP Super TET 2026 Mock${auto?' · समय समाप्त, अपने आप सबमिट':''}${S.tabs?' · टैब बदले: '+S.tabs:''}</div></div><div style="display:flex;gap:8px;flex-wrap:wrap"><button class="b b-bl" id="pdf">⬇ प्रश्न-पत्र + उत्तर-कुंजी (PDF)</button><button class="b b-wh" id="again">दोबारा दें</button></div></div>`;
 h+=`<div class="kp"><div><b style="color:var(--hd)">${tot} <small style="font-size:14px;color:var(--mu)">/ 360</small></b><span>कुल अंक</span></div><div><b style="color:var(--gr)">${c}</b><span>सही (+${c*3})</span></div><div><b style="color:var(--rd)">${w}</b><span>गलत (−${w})</span></div><div><b>${sk}</b><span>छोड़े</span></div><div><b>${acc}%</b><span>सटीकता</span></div></div>`;
 h+=`<div class="tw"><table class="t"><tr><th>Section</th><th>प्रश्न</th><th>किए</th><th>सही</th><th>गलत</th><th>अंक</th></tr>${per.map(p=>`<tr><td>${esc(p[0])}</td><td>${p[1]}</td><td>${p[2]}</td><td>${p[3]}</td><td>${p[4]}</td><td><b>${p[5]}</b></td></tr>`).join('')}<tr><th>कुल</th><th>120</th><th>${c+w}</th><th>${c}</th><th>${w}</th><th>${tot}</th></tr></table></div>`;
 h+=`<div class="filt" id="filt"><button class="on" data-f="all">सभी (120)</button><button data-f="c">सही (${c})</button><button data-f="w">गलत (${w})</button><button data-f="s">छोड़े (${sk})</button></div><div id="sols"></div>`;
 $('resw').innerHTML=h;names();go('s-res');sols('all');
 $('pdf').onclick=printPaper;
 $('again').onclick=()=>{try{localStorage.removeItem(KEY)}catch(e){}location.reload()};
 $('filt').onclick=e=>{const b=e.target.closest('[data-f]');if(!b)return;$('filt').querySelectorAll('button').forEach(x=>x.classList.toggle('on',x===b));sols(b.dataset.f)}}
function sols(f){$('sols').innerHTML=Q.map((q,i)=>{const a=S.ans[i],k=!a?'s':(a===q.a?'c':'w');if(f!=='all'&&f!==k)return '';
 const tg={s:'<span class="tg s">छोड़ा</span>',c:'<span class="tg c">सही +3</span>',w:'<span class="tg w">गलत −1</span>'}[k];
 return `<div class="sol"><div class="h"><span>प्रश्न ${i+1} · ${esc(SECS[secOf(i)].n)}</span>${tg}</div>${q.p?'<div style="color:var(--mu);font-size:13px">[गद्यांश आधारित]</div>':''}<div>${esc(q.q)}</div>${q.o.map((o,j)=>`<div class="o ${L[j]===q.a?'ok':(L[j]===a?'bad':'')}">${j+1}. ${esc(o)}${L[j]===q.a?' ✓':(L[j]===a?' ✗':'')}</div>`).join('')}${q.e?`<div class="ex"><b>व्याख्या:</b> ${esc(q.e)}</div>`:''}</div>`}).join('')}
function printPaper(){let h='<h1>UP Super TET 2026 — लाइव मॉक (XL Study)</h1><p style="text-align:center;margin:0 0 10px">120 प्रश्न · 360 अंक · 120 मिनट · सही +3 · गलत −1</p><div class="cols">';
 Q.forEach((q,i)=>{h+=`<div class="pq"><b>${i+1}.</b> ${q.p&&!(Q[i-1]&&Q[i-1].p===q.p)?'<i>'+esc(q.p)+'</i><br>':''}${esc(q.q)}<br>(1) ${esc(q.o[0])} &nbsp; (2) ${esc(q.o[1])}<br>(3) ${esc(q.o[2])} &nbsp; (4) ${esc(q.o[3])}</div>`});
 h+='</div><h1 style="margin-top:14px;break-before:page">उत्तर-कुंजी</h1><div class="key">'+Q.map((q,i)=>`<div>${i+1} - (${L.indexOf(q.a)+1})</div>`).join('')+'</div>';
 $('printArea').innerHTML=h;window.print()}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){close();side(0)}});

load();
if(S.end){names();if(S.done)finish(false);else start()}
}
</script>
</body>
</html>
