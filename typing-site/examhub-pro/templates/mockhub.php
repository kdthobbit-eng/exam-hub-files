<?php
/* Mock Test hub: Super TET, UP TGT, UP PGT. TGT/PGT open a subject list; a subject opens its live mock. */
if (!defined('ABSPATH')) exit;
nocache_headers();
$x = sanitize_key($_GET['x'] ?? '');
if (!in_array($x, ['tgt', 'pgt'], true)) $x = '';
$logo = esc_url(get_template_directory_uri() . '/assets/upsssc-alert-logo.svg');
$stet = eh_stm_round();
$rounds = array_values(array_filter(eh_stm_rounds(), function ($r) use ($x) { return ($r['series'] ?? '') === $x; }));
$cnt = function ($s) { return count(eh_tp_subjects()[$s]); };
$info = $x ? eh_tp_info($x) : null;
?><!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0f2a4d">
<title><?php echo $x ? esc_html($info['title'] . ' — विषयवार लाइव मॉक') : 'मॉक टेस्ट — Super TET, TGT, PGT'; ?> | UPSSSC ALERT × XL Study</title>
<link rel="icon" href="<?php echo $logo; ?>">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap">
<style>
:root{--nv:#0f2a4d;--pr:#1d63c7;--gd:#f5b53f;--bg:#f2f5f9;--ln:#dbe3ec;--tx:#17202b;--mu:#5f6b7a}
*{box-sizing:border-box}html,body{margin:0}
body{font-family:'Noto Sans Devanagari',system-ui,sans-serif;color:var(--tx);background:var(--bg);font-size:15px;line-height:1.5}
a{color:inherit;text-decoration:none}
.hd{background:#fff;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 18px;box-shadow:0 1px 0 var(--ln),0 4px 14px rgba(15,42,77,.06)}
.brand{display:flex;align-items:center;gap:10px}.brand img{width:44px;height:44px;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.12)}
.brand b{display:block;font-family:Poppins,sans-serif;font-size:16px;color:var(--nv);line-height:1.15}.brand span{display:block;font-size:12px;color:var(--mu)}
.hm{width:38px;height:38px;border-radius:50%;border:1px solid var(--ln);display:flex;align-items:center;justify-content:center}.hm svg{width:20px;height:20px;fill:none;stroke:var(--nv);stroke-width:2}
.hero{background:radial-gradient(420px 260px at 100% 0,rgba(245,181,63,.28),transparent 70%),linear-gradient(125deg,#071534,#123a86 60%,#1d74dc);color:#fff;padding:34px 18px 70px}
.hin{max-width:1040px;margin:0 auto}
.crumb{font-size:13px;opacity:.85;margin-bottom:8px}.crumb a{text-decoration:underline}
.hero h1{font-family:Poppins,'Noto Sans Devanagari',sans-serif;font-size:clamp(26px,5vw,40px);margin:0 0 6px;line-height:1.2}.hero h1 em{font-style:normal;color:var(--gd)}
.hero p{margin:0;font-size:15.5px;opacity:.92;max-width:640px}
.pills{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.pills span{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:30px;padding:5px 12px;font-size:13px}
.wrap{max-width:1040px;margin:-44px auto 0;padding:0 14px 40px}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.c{background:#fff;border-radius:18px;box-shadow:0 10px 30px -12px rgba(15,42,77,.25);overflow:hidden;display:flex;flex-direction:column;transition:transform .15s}
.c:hover{transform:translateY(-3px)}
.ct{padding:20px 20px 16px;color:#fff;position:relative}
.c1 .ct{background:linear-gradient(125deg,#0f2a4d,#1d63c7)}.c2 .ct{background:linear-gradient(125deg,#5b2a86,#8e44ad)}.c3 .ct{background:linear-gradient(125deg,#0b6b4f,#16a34a)}
.ct small{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.5px;background:rgba(255,255,255,.18);border-radius:20px;padding:3px 10px}
.ct h2{font-family:Poppins,'Noto Sans Devanagari',sans-serif;font-size:24px;margin:10px 0 2px}.ct p{margin:0;font-size:13.5px;opacity:.9}
.cb{padding:16px 20px 20px;display:flex;flex-direction:column;gap:10px;flex:1}
.cb ul{margin:0;padding:0;list-style:none;font-size:14px;color:var(--mu)}.cb li{padding:3px 0}.cb li b{color:var(--tx)}
.go{margin-top:auto;display:block;text-align:center;background:linear-gradient(135deg,#ffe29a,#f5b53f);color:#3b2a00;font-weight:800;border-radius:12px;padding:12px;font-size:16px}
.live{display:inline-flex;align-items:center;gap:6px;color:#16a34a;font-weight:700;font-size:13px}.live i{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.2)}
.sec{background:#fff;border-radius:18px;box-shadow:0 10px 30px -12px rgba(15,42,77,.25);padding:20px}
.sec h2{margin:0 0 4px;font-size:20px;color:var(--nv)}.sec .sub{margin:0 0 16px;color:var(--mu);font-size:14px}
.subs{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}
.s{border:1.5px solid var(--ln);border-radius:14px;padding:14px 14px 12px;display:flex;flex-direction:column;gap:4px;background:#fbfcfe;transition:border-color .15s,box-shadow .15s}
.s:hover{border-color:var(--pr);box-shadow:0 6px 16px -8px rgba(29,99,199,.5)}
.s b{font-size:17px;color:var(--nv)}.s span{font-size:12.5px;color:var(--mu)}
.s em{font-style:normal;font-size:12.5px;font-weight:700;margin-top:4px}.s em.on{color:#16a34a}.s em.soon{color:#b7791f}
.s.dis{opacity:.75}
.pat{margin-top:18px;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px}
.pat div{background:#fff;border-radius:14px;padding:12px;text-align:center;box-shadow:0 4px 14px -8px rgba(15,42,77,.3)}.pat b{display:block;font-family:Poppins,sans-serif;font-size:22px;color:var(--nv)}.pat span{font-size:13px;color:var(--mu)}
.foot{text-align:center;color:var(--mu);font-size:12.5px;margin-top:26px;display:flex;align-items:center;justify-content:center;gap:8px}.foot img{width:22px;height:22px;border-radius:50%}
</style>
</head>
<body>
<header class="hd"><div class="brand"><a class="hm" href="<?php echo esc_url(home_url('/')); ?>" title="होम"><svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5M5.5 9.5V20h5v-6h3v6h5V9.5"/></svg></a><img src="<?php echo $logo; ?>" alt="UPSSSC ALERT"><div><b>UPSSSC ALERT</b><span>Online Mock Test · xlstudy.in</span></div></div></header>
<?php if (!$x): ?>
<div class="hero"><div class="hin">
 <h1>लाइव <em>मॉक टेस्ट</em></h1>
 <p>अपनी परीक्षा चुनें — असली परीक्षा जैसा कंप्यूटर आधारित टेस्ट, सर्वर पर चलने वाली घड़ी, तुरंत स्कोर, रैंक, परसेंटाइल और हर प्रश्न की व्याख्या।</p>
 <div class="pills"><span>✅ बिल्कुल फ्री</span><span>📝 120 प्रश्न · 360 अंक</span><span>⏱ 2 घंटे</span><span>🌐 हिंदी + English</span></div>
</div></div>
<div class="wrap"><div class="cards">
 <a class="c c1" href="<?php echo esc_url(home_url('/super-tet-mock/')); ?>"><div class="ct"><small>प्राथमिक स्तर · कक्षा 1–5</small><h2>UP Super TET</h2><p>सहायक अध्यापक भर्ती परीक्षा</p></div>
  <div class="cb"><?php if (eh_stm_status($stet) === 'live'): ?><span class="live"><i></i>अभी लाइव</span><?php endif; ?><ul><li><b>120</b> प्रश्न · <b>360</b> अंक · <b>2</b> घंटे</li><li>10 सेक्शन, नए पैटर्न पर</li><li>पिछले मॉक के परिणाम भी देखें</li></ul><span class="go">मॉक दें →</span></div></a>
 <a class="c c2" href="<?php echo esc_url(add_query_arg('x', 'tgt', home_url('/mock-test/'))); ?>"><div class="ct"><small>स्नातक श्रेणी · <?php echo $cnt('tgt'); ?> विषय</small><h2>UP TGT</h2><p>सहायक अध्यापक (प्रशिक्षित स्नातक) · विज्ञापन 07/2026</p></div>
  <div class="cb"><span class="live"><i></i>अभी लाइव</span><ul><li><b>30</b> सामान्य अध्ययन + <b>90</b> विषय</li><li>सभी <b><?php echo $cnt('tgt'); ?></b> विषयों के अलग मॉक</li><li><b>+3 / −1</b> अंकन · 2 घंटे</li></ul><span class="go">विषय चुनें →</span></div></a>
 <a class="c c3" href="<?php echo esc_url(add_query_arg('x', 'pgt', home_url('/mock-test/'))); ?>"><div class="ct"><small>प्रवक्ता · <?php echo $cnt('pgt'); ?> विषय</small><h2>UP PGT</h2><p>प्रवक्ता (स्नातकोत्तर) · विज्ञापन 06/2026</p></div>
  <div class="cb"><span class="live"><i></i>अभी लाइव</span><ul><li><b>30</b> सामान्य अध्ययन + <b>90</b> विषय</li><li>सभी <b><?php echo $cnt('pgt'); ?></b> विषयों के अलग मॉक</li><li><b>+3 / −1</b> अंकन · 2 घंटे</li></ul><span class="go">विषय चुनें →</span></div></a>
</div>
<?php else: ?>
<div class="hero"><div class="hin">
 <div class="crumb"><a href="<?php echo esc_url(home_url('/mock-test/')); ?>">मॉक टेस्ट</a> › <?php echo esc_html($info['short']); ?></div>
 <h1><?php echo esc_html($info['title']); ?> <em>विषयवार लाइव मॉक</em></h1>
 <p><?php echo esc_html($info['post'] . ' · ' . $info['adv'] . ' · ' . $info['level']); ?> — अपना विषय चुनें।</p>
 <div class="pills"><span>📝 30 सामान्य अध्ययन + 90 विषय</span><span>🎯 360 अंक · +3 / −1</span><span>⏱ 2 घंटे</span><span>✅ फ्री</span></div>
</div></div>
<div class="wrap"><div class="sec"><h2>विषय चुनें</h2><p class="sub">विषय पर क्लिक करें, फिर लॉग-इन करके मॉक शुरू करें। लॉग-इन पेज पर भी विषय बदल सकते हैं।</p>
 <div class="subs"><?php foreach ($rounds as $r): $ok = eh_tp_ready($r); ?>
  <a class="s<?php echo $ok ? '' : ' dis'; ?>" href="<?php echo esc_url(eh_tp_url($r)); ?>"><b><?php echo esc_html($r['sub']); ?></b><span><?php echo esc_html($r['sub_en']); ?></span><em class="<?php echo $ok ? 'on' : 'soon'; ?>"><?php echo $ok ? '● लाइव — मॉक दें →' : 'जल्द आ रहा है'; ?></em></a>
 <?php endforeach; ?></div></div>
 <div class="pat"><div><b>120</b><span>कुल प्रश्न</span></div><div><b>30</b><span>सामान्य अध्ययन</span></div><div><b>90</b><span>विषय</span></div><div><b>360</b><span>पूर्णांक</span></div><div><b>120</b><span>मिनट</span></div></div>
<?php endif; ?>
 <div class="foot"><img src="<?php echo $logo; ?>" alt=""> UPSSSC ALERT × XL Study · xlstudy.in</div>
</div>
</body>
</html>
