<?php
/* STET Mock Test — online test (Testbook-app style). Unlisted page, noindex. */
if (!defined('ABSPATH')) exit;
header('X-Robots-Tag: noindex, nofollow', true);
nocache_headers();
$eh_mocks = @file_get_contents(EH_DIR . '/data/stet-mocks.json');
if (!$eh_mocks) $eh_mocks = '{}';
?><!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>STET Mock Test — Exam Hub</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap">
<style>
:root{--bl:#1a73e8;--bl2:#e8f1fe;--tx:#1f2a37;--mu:#6b7785;--ln:#e3e8ef;--bg:#f4f6f9;--gr:#1f9d55;--rd:#e5484d;--pu:#7c4dff;--or:#f59f00}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0;height:100%}
body{font-family:Inter,'Noto Sans Devanagari',system-ui,sans-serif;color:var(--tx);background:var(--bg);font-size:15px}
button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}
.hide{display:none!important}
svg{display:block}
/* ---------- landing ---------- */
.land{max-width:720px;margin:0 auto;padding:0 0 30px}
.lhead{background:linear-gradient(135deg,#123d7a,#1a73e8);color:#fff;padding:22px 18px 60px}
.lhead small{opacity:.85;font-size:12px;letter-spacing:.6px;text-transform:uppercase}
.lhead h1{margin:6px 0 4px;font-size:22px}
.lhead p{margin:0;opacity:.9;font-size:13.5px}
.tlist{margin:-44px 14px 0;display:flex;flex-direction:column;gap:12px}
.tc{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(16,40,80,.08);padding:14px 16px;display:flex;align-items:center;gap:14px}
.tc .ic{width:46px;height:46px;border-radius:10px;background:var(--bl2);color:var(--bl);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;flex:none}
.tc .md{flex:1;min-width:0}
.tc b{display:block;font-size:15.5px}
.tc .mt{color:var(--mu);font-size:12.5px;margin-top:3px;display:flex;flex-wrap:wrap;gap:4px 10px}
.tag{display:inline-block;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;margin-top:6px}
.t-h{background:#fdecec;color:#c62828}.t-m{background:#fff4e0;color:#b26a00}
.go{background:var(--bl);color:#fff;font-weight:600;padding:9px 18px;border-radius:8px;font-size:14px}
.go.ghost{background:var(--bl2);color:var(--bl)}
.note{margin:16px 14px 0;font-size:12.5px;color:var(--mu);text-align:center}
/* ---------- instructions ---------- */
.inst{max-width:760px;margin:0 auto;background:#fff;min-height:100%;display:flex;flex-direction:column}
.ibar{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid var(--ln)}
.ibar b{font-size:16px}
.ibody{padding:16px 18px;flex:1;line-height:1.7;font-size:14.5px}
.ibody h3{margin:4px 0 10px;font-size:16px}
.igrid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:12px 0 16px}
.igrid div{background:var(--bg);border-radius:10px;padding:10px;text-align:center;font-size:12px;color:var(--mu)}
.igrid b{display:block;font-size:18px;color:var(--tx)}
.ibody ol{padding-left:20px;margin:6px 0}
.lg{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:12px 0}
.lg div{display:flex;align-items:center;gap:8px;font-size:13px}
.ifoot{padding:12px 16px;border-top:1px solid var(--ln);display:flex;align-items:center;justify-content:space-between;gap:10px;position:sticky;bottom:0;background:#fff}
.ifoot label{font-size:13px;display:flex;gap:8px;align-items:flex-start}
.ifoot .go:disabled{opacity:.45}
/* ---------- palette bubbles ---------- */
.b{width:38px;height:38px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;position:relative;border:1px solid #cfd6e0;background:#fff;color:var(--tx);flex:none}
.b.na{background:var(--rd);border-color:var(--rd);color:#fff}
.b.an{background:var(--gr);border-color:var(--gr);color:#fff}
.b.mr{background:var(--pu);border-color:var(--pu);color:#fff}
.b.am{background:var(--pu);border-color:var(--pu);color:#fff}
.b.am::after{content:"";position:absolute;right:-1px;bottom:-1px;width:12px;height:12px;border-radius:50%;background:var(--gr);border:2px solid #fff}
.b.cur{box-shadow:0 0 0 2px #fff,0 0 0 4px var(--bl)}
.b.sm{width:24px;height:24px;font-size:11px}
/* ---------- test ---------- */
.app{height:100%;display:flex;flex-direction:column}
.hdr{background:#fff;display:flex;align-items:center;gap:10px;padding:8px 10px 8px 12px;border-bottom:1px solid var(--ln);flex:none}
.hdr .tn{flex:1;min-width:0}
.hdr .tn b{display:block;font-size:14.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hdr .tn span{font-size:11.5px;color:var(--mu)}
.clock{display:flex;align-items:center;gap:6px;background:var(--bg);border-radius:20px;padding:6px 11px;font-weight:700;font-size:14px;font-variant-numeric:tabular-nums}
.clock.low{background:#fdecec;color:var(--rd)}
.ib{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--tx)}
.ib:active{background:var(--bg)}
.sub{background:var(--bl);color:#fff;font-weight:600;font-size:13.5px;padding:8px 14px;border-radius:8px}
.secs{background:#fff;display:flex;gap:8px;overflow-x:auto;padding:8px 12px;border-bottom:1px solid var(--ln);flex:none;scrollbar-width:none}
.secs::-webkit-scrollbar{display:none}
.chip{white-space:nowrap;border:1px solid var(--ln);border-radius:20px;padding:6px 13px;font-size:13px;color:var(--mu);background:#fff}
.chip.on{background:var(--bl);border-color:var(--bl);color:#fff;font-weight:600}
.body{flex:1;display:flex;min-height:0}
.qcol{flex:1;display:flex;flex-direction:column;min-width:0;background:#fff}
.qmeta{display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px dashed var(--ln);flex:none}
.qmeta .qn{font-weight:700;font-size:15px}
.mk{font-size:12px;font-weight:600;padding:2px 8px;border-radius:6px}
.mk.p{background:#e6f6ed;color:var(--gr)}.mk.n{background:#fdecec;color:var(--rd)}
.qmeta .qt{margin-left:auto;display:flex;align-items:center;gap:5px;color:var(--mu);font-size:12.5px;font-variant-numeric:tabular-nums}
.qscroll{flex:1;overflow:auto;padding:14px 14px 20px}
.qtext{line-height:1.8;font-size:15.5px}
.qtext .st{padding-left:4px}
.qtext i{font-style:normal;display:block;background:#f7f9fc;border-left:3px solid var(--bl);padding:8px 10px;border-radius:4px;margin:4px 0 8px;font-size:14.5px}
table.ml{border-collapse:collapse;width:100%;margin:8px 0;font-size:14px}
table.ml th,table.ml td{border:1px solid var(--ln);padding:6px 9px;text-align:left;vertical-align:top}
table.ml th{background:#f2f5f9;font-weight:600}
.opts{margin-top:16px;display:flex;flex-direction:column;gap:10px}
.op{display:flex;align-items:flex-start;gap:12px;border:1.5px solid var(--ln);border-radius:12px;padding:11px 13px;text-align:left;line-height:1.6;font-size:15px;background:#fff;width:100%}
.op .l{width:28px;height:28px;border-radius:50%;border:1.5px solid #c3ccd8;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex:none;color:var(--mu)}
.op.on{border-color:var(--bl);background:var(--bl2)}
.op.on .l{background:var(--bl);border-color:var(--bl);color:#fff}
.op.ok{border-color:var(--gr);background:#eaf7ef}.op.ok .l{background:var(--gr);border-color:var(--gr);color:#fff}
.op.bad{border-color:var(--rd);background:#fdeeee}.op.bad .l{background:var(--rd);border-color:var(--rd);color:#fff}
.op .yt{display:block;font-size:11.5px;font-weight:600;margin-top:2px}
.ftr{background:#fff;border-top:1px solid var(--ln);display:flex;align-items:center;gap:8px;padding:9px 10px;flex:none;padding-bottom:max(9px,env(safe-area-inset-bottom))}
.ftr .ob{border:1.5px solid #cfd6e0;border-radius:8px;padding:10px 12px;font-size:13.5px;font-weight:600;color:var(--tx);white-space:nowrap}
.ftr .ob.mr{color:var(--pu);border-color:#cdbbff}
.ftr .sv{margin-left:auto;background:var(--bl);color:#fff;border-radius:8px;padding:11px 18px;font-weight:600;font-size:14px;white-space:nowrap}
.ftr .nav{width:42px;height:42px;border:1.5px solid #cfd6e0;border-radius:8px;display:flex;align-items:center;justify-content:center}
.ftr .nav:disabled{opacity:.35}
/* side / sheet */
.side{width:330px;background:#fff;border-left:1px solid var(--ln);display:flex;flex-direction:column;flex:none}
.shd{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid var(--ln)}
.av{width:38px;height:38px;border-radius:50%;background:var(--bl2);color:var(--bl);display:flex;align-items:center;justify-content:center;font-weight:700}
.shd .nm{flex:1}.shd .nm b{display:block;font-size:14px}.shd .nm span{font-size:12px;color:var(--mu)}
.cnts{display:grid;grid-template-columns:1fr 1fr;gap:10px 8px;padding:12px 14px;border-bottom:1px solid var(--ln);font-size:12.5px;color:var(--mu)}
.cnts div{display:flex;align-items:center;gap:8px}
.cnts .w{grid-column:1/3}
.pscroll{flex:1;overflow:auto;padding:4px 14px 14px}
.pscroll h4{font-size:13px;margin:14px 0 10px;color:var(--tx);display:flex;justify-content:space-between}
.pscroll h4 span{color:var(--mu);font-weight:500}
.pg{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;justify-items:center}
.sft{padding:12px 14px;border-top:1px solid var(--ln);padding-bottom:max(12px,env(safe-area-inset-bottom))}
.sft .go{width:100%;padding:12px}
.scrim{position:fixed;inset:0;background:rgba(15,25,40,.45);z-index:60}
@media (max-width:999px){
 .side{position:fixed;left:0;right:0;bottom:0;width:auto;max-height:86vh;border-radius:18px 18px 0 0;z-index:70;transform:translateY(102%);transition:transform .22s ease;border-left:0;box-shadow:0 -6px 24px rgba(0,0,0,.18)}
 .side.open{transform:none}
 .side .grab{width:40px;height:4px;border-radius:4px;background:#d5dbe3;margin:8px auto 0}
}
@media (min-width:1000px){.side .grab,.pbtn{display:none!important}.qscroll{padding:20px 28px}.ftr{padding:10px 20px}}
/* dialogs */
.dlg{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;z-index:80;width:min(460px,calc(100% - 28px));max-height:88vh;overflow:auto;padding:18px}
.dlg h3{margin:0 0 4px;font-size:17px}
.dlg p{margin:0 0 12px;color:var(--mu);font-size:13px}
.stab{width:100%;border-collapse:collapse;font-size:12.5px;margin-bottom:14px}
.stab th,.stab td{padding:7px 5px;border-bottom:1px solid var(--ln);text-align:center}
.stab th{color:var(--mu);font-weight:600;font-size:11.5px}
.stab td:first-child,.stab th:first-child{text-align:left}
.dact{display:flex;gap:10px}
.dact button{flex:1;padding:12px;border-radius:10px;font-weight:600}
.dact .a{background:var(--bg)}.dact .b2{background:var(--bl);color:#fff}
/* result */
.res{max-width:820px;margin:0 auto;padding-bottom:24px}
.rhead{background:linear-gradient(135deg,#123d7a,#1a73e8);color:#fff;padding:16px 16px 70px}
.rhead small{opacity:.85;font-size:12px}
.rhead h2{margin:4px 0 0;font-size:19px}
.card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(16,40,80,.07);margin:12px 14px;padding:16px}
.card.top{margin-top:-56px}
.scorebox{display:flex;align-items:center;gap:18px}
.ring{width:108px;height:108px;flex:none;position:relative}
.ring .c{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.ring .c b{font-size:24px}.ring .c span{font-size:11.5px;color:var(--mu)}
.sline{flex:1;display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sline div{font-size:12px;color:var(--mu)}
.sline b{display:block;font-size:18px;color:var(--tx)}
.ctitle{font-size:15px;font-weight:700;margin:0 0 12px}
.bars{display:flex;height:10px;border-radius:6px;overflow:hidden;background:var(--bg);margin:6px 0 10px}
.bars i{display:block}
.blg{display:flex;gap:14px;flex-wrap:wrap;font-size:12px;color:var(--mu)}
.blg span::before{content:"";display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:5px;background:var(--c)}
.srow{padding:11px 0;border-bottom:1px solid var(--ln)}
.srow:last-child{border-bottom:0}
.srow .h{display:flex;justify-content:space-between;font-size:14px;font-weight:600}
.srow .h span{color:var(--bl)}
.srow .d{display:flex;gap:12px;font-size:12px;color:var(--mu);margin-top:4px;flex-wrap:wrap}
.ract{display:flex;gap:10px;margin:14px}
.ract button{flex:1;padding:13px;border-radius:10px;font-weight:600}
.ract .p1{background:var(--bl);color:#fff}.ract .p2{background:#fff;border:1.5px solid var(--bl);color:var(--bl)}
.fchips{display:flex;gap:8px;overflow-x:auto;padding:8px 12px;background:#fff;border-bottom:1px solid var(--ln);flex:none;scrollbar-width:none}
.fchips::-webkit-scrollbar{display:none}
.smt{display:none}
@media (max-width:480px){.lgt{display:none}.smt{display:inline}.ftr{gap:6px;padding-left:8px;padding-right:8px}.ftr .ob{padding:10px 10px;font-size:13px}.ftr .sv{padding:11px 14px;font-size:13.5px}.ftr .nav{width:40px}.hdr{gap:6px}.sub{padding:8px 11px}}
.foot{text-align:center;font-size:12px;color:var(--mu);margin-top:6px}
</style>
</head>
<body>

<!-- LANDING -->
<div id="vLand" class="land">
 <div class="lhead"><small>Exam Hub • Super TET (Primary)</small><h1>STET Mock Test Series</h1><p>सहायक अध्यापक (कक्षा 1–5) • असली परीक्षा जैसा ऑनलाइन टेस्ट</p></div>
 <div class="tlist" id="tlist"></div>
 <div class="note">Telegram : @examhub2026</div>
</div>

<!-- INSTRUCTIONS -->
<div id="vInst" class="inst hide">
 <div class="ibar"><button class="ib" onclick="goLand()" aria-label="Back"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button><b id="iTitle"></b></div>
 <div class="ibody">
  <div class="igrid"><div><b>120</b>प्रश्न</div><div><b>360</b>अंक</div><div><b>120</b>मिनट</div></div>
  <h3>सामान्य निर्देश</h3>
  <ol>
   <li>हर सही उत्तर पर <b>+3</b> अंक मिलेंगे और हर गलत उत्तर पर <b>1</b> अंक कटेगा।</li>
   <li>टाइमर ऊपर दिखेगा। समय खत्म होते ही टेस्ट अपने-आप सबमिट हो जाएगा।</li>
   <li>उत्तर चुनकर <b>Save &amp; Next</b> दबाएँ, तभी उत्तर सेव होगा।</li>
   <li>बाद में देखने के लिए <b>Mark for Review &amp; Next</b> दबाएँ। अगर उत्तर चुना है, तो वह भी गिना जाएगा।</li>
   <li>किसी भी प्रश्न पर जाने के लिए प्रश्न-सूची (<b>⋮⋮</b> बटन) खोलें।</li>
  </ol>
  <div class="lg">
   <div><span class="b sm an">1</span>Answered</div><div><span class="b sm na">2</span>Not Answered</div>
   <div><span class="b sm">3</span>Not Visited</div><div><span class="b sm mr">4</span>Marked for Review</div>
   <div style="grid-column:1/3"><span class="b sm am">5</span>Answered &amp; Marked (गिना जाएगा)</div>
  </div>
 </div>
 <div class="ifoot"><label><input type="checkbox" id="agree"> मैंने सभी निर्देश पढ़ लिए हैं।</label><button class="go" id="startBtn" disabled>Start Test</button></div>
</div>

<!-- TEST / SOLUTIONS -->
<div id="vApp" class="app hide">
 <div class="hdr">
  <div class="tn"><b id="hTitle"></b><span id="hSub"></span></div>
  <div class="clock" id="clock"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2M9 2h6"/></svg><span id="clockT">120:00</span></div>
  <button class="ib pbtn" onclick="openPal()" aria-label="Question palette"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></button>
  <button class="sub" id="hBtn"></button>
 </div>
 <div class="secs" id="secs"></div>
 <div class="fchips hide" id="fchips"></div>
 <div class="body">
  <div class="qcol">
   <div class="qmeta"><span class="qn" id="qn"></span><span class="mk p">+3</span><span class="mk n">−1</span><span class="qt" id="qt"></span></div>
   <div class="qscroll" id="qscroll"><div class="qtext" id="qtext"></div><div class="opts" id="opts"></div></div>
   <div class="ftr" id="ftrT">
    <button class="nav" id="bPrev" aria-label="Previous"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></button>
    <button class="ob mr" id="bMark"><span class="lgt">Mark for Review &amp; Next</span><span class="smt">Mark &amp; Next</span></button>
    <button class="ob" id="bClear">Clear</button>
    <button class="sv" id="bSave">Save &amp; Next</button>
   </div>
   <div class="ftr hide" id="ftrS">
    <button class="nav" id="sPrev" aria-label="Previous"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg></button>
    <button class="ob" onclick="showResult()">Result पर जाएँ</button>
    <button class="sv" id="sNext">Next</button>
   </div>
  </div>
  <div class="side" id="side">
   <div class="grab"></div>
   <div class="shd"><div class="av">EH</div><div class="nm"><b>Exam Hub Student</b><span id="pSub"></span></div><button class="ib pbtn" onclick="closePal()" aria-label="Close"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 6l12 12M18 6L6 18"/></svg></button></div>
   <div class="cnts" id="cnts"></div>
   <div class="pscroll" id="pal"></div>
   <div class="sft" id="sft"><button class="go" onclick="askSubmit()">Submit Test</button></div>
  </div>
 </div>
</div>

<!-- RESULT -->
<div id="vRes" class="res hide"></div>

<div class="scrim hide" id="scrim" onclick="closeAll()"></div>
<div class="dlg hide" id="dlg"></div>

<script>
const MOCKS=<?php echo $eh_mocks; ?>;
const L="ABCD",TOTAL=7200;
let mk=null,Q=[],S=[],cur=0,left=TOTAL,tick=null,mode="test",qStart=0,filt="all",order=[];
const $=id=>document.getElementById(id);
const SEC_SHORT={"सामान्य ज्ञान एवं समसामयिकी":"सामान्य ज्ञान","हिन्दी भाषा":"हिन्दी","संस्कृत भाषा":"संस्कृत","General English":"English","पर्यावरण एवं सामाजिक अध्ययन":"पर्यावरण","जीवन कौशल / प्रबंधन":"जीवन कौशल"};
const sname=s=>SEC_SHORT[s]||s;
const pad=n=>String(n).padStart(2,"0");
const mmss=t=>pad(Math.floor(t/60))+":"+pad(t%60);
function fmt(t){
 let out="",tbl=[];const fl=()=>{if(tbl.length){out+="<table class='ml'>"+tbl.map((r,j)=>j?`<tr><td>${r[0]}</td><td>${r[1]}</td></tr>`:`<tr><th>${r[0]}</th><th>${r[1]}</th></tr>`).join("")+"</table>";tbl=[]}};
 t.split("\n").forEach(ln=>{if(ln.includes("||")){tbl.push(ln.split("||").map(x=>x.trim()));return}fl();out+=`<div class="${/^[1-5]\./.test(ln)?"st":""}">${ln}</div>`});
 fl();return out;
}
function store(k,v){try{v===undefined?localStorage.removeItem(k):localStorage.setItem(k,JSON.stringify(v))}catch(e){}}
function load(k){try{return JSON.parse(localStorage.getItem(k)||"null")}catch(e){return null}}

/* landing */
function goLand(){["vInst","vApp","vRes"].forEach(i=>$(i).classList.add("hide"));$("vLand").classList.remove("hide");drawLand();window.scrollTo(0,0)}
function drawLand(){
 const t=$("tlist");t.innerHTML="";
 Object.keys(MOCKS).forEach(k=>{
  const m=MOCKS[k],last=load("stet_res_"+k);
  const d=document.createElement("div");d.className="tc";
  d.innerHTML=`<div class="ic">${k}</div><div class="md"><b>STET Mock Test ${k}</b><div class="mt"><span>120 प्रश्न</span><span>360 अंक</span><span>120 मिनट</span></div><span class="tag ${m.lvl==="कठिन"?"t-h":"t-m"}">${m.lvl==="कठिन"?"Hard":"Moderate"}</span>${last?`<span class="tag" style="background:#eaf7ef;color:#1f9d55;margin-left:6px">पिछला स्कोर ${last.score}</span>`:""}</div>`;
  const b=document.createElement("button");b.className="go"+(last?" ghost":"");b.textContent=last?"Reattempt":"Start";b.onclick=()=>openInst(k);
  d.appendChild(b);t.appendChild(d);
 });
}
function openInst(k){mk=k;$("vLand").classList.add("hide");$("vInst").classList.remove("hide");$("iTitle").textContent=`STET Mock Test ${k}`;$("agree").checked=false;$("startBtn").disabled=true;window.scrollTo(0,0)}
$("agree").onchange=e=>$("startBtn").disabled=!e.target.checked;
$("startBtn").onclick=startTest;

/* test */
function startTest(){
 Q=MOCKS[mk].q;S=Q.map(()=>({v:false,a:null,m:false,sel:null,t:0}));cur=0;left=TOTAL;mode="test";
 $("vInst").classList.add("hide");$("vApp").classList.remove("hide");
 $("hTitle").textContent=`Mock Test ${mk}`;$("hSub").textContent="STET • "+(MOCKS[mk].lvl==="कठिन"?"Hard":"Moderate");
 $("pSub").textContent=`Mock Test ${mk}`;$("hBtn").textContent="Submit";$("hBtn").onclick=askSubmit;
 $("clock").classList.remove("hide");$("secs").classList.remove("hide");$("fchips").classList.add("hide");
 $("ftrT").classList.remove("hide");$("ftrS").classList.add("hide");$("sft").classList.remove("hide");
 order=Q.map((q,i)=>i);drawSecs();buildPal();show(0);
 clearInterval(tick);tick=setInterval(()=>{left--;S[cur].t++;$("clockT").textContent=mmss(Math.max(left,0));$("clock").classList.toggle("low",left<=300);
  $("qt").innerHTML=clockIco()+mmss(S[cur].t);if(left<=0)finish()},1000);
 $("clockT").textContent=mmss(left);
}
function clockIco(){return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>'}
function secList(){const a=[];Q.forEach((q,i)=>{if(!a.length||a[a.length-1].n!==q[0])a.push({n:q[0],f:i,c:0});a[a.length-1].c++});return a}
function drawSecs(){
 const s=$("secs");s.innerHTML="";
 secList().forEach(x=>{const c=document.createElement("button");c.className="chip";c.dataset.f=x.f;c.dataset.n=x.n;c.textContent=sname(x.n);c.onclick=()=>show(x.f);s.appendChild(c)});
}
function syncSec(){const n=Q[cur][0];[...$("secs").children].forEach(c=>{const on=c.dataset.n===n;c.classList.toggle("on",on);if(on)c.scrollIntoView({inline:"center",block:"nearest",behavior:"smooth"})})}
function st(i){
 if(mode==="sol"){const a=S[i].a;return a===null?"":(a===Q[i][3]?"an":"na")}
 const s=S[i];if(s.a!==null&&s.m)return"am";if(s.m)return"mr";if(s.a!==null)return"an";if(s.v)return"na";return""}
function buildPal(){
 const p=$("pal");p.innerHTML="";
 secList().forEach(x=>{
  const h=document.createElement("h4");h.innerHTML=`${x.n}<span>${x.c} प्रश्न</span>`;p.appendChild(h);
  const g=document.createElement("div");g.className="pg";
  for(let i=x.f;i<x.f+x.c;i++){const b=document.createElement("button");b.className="b";b.id="pb"+i;b.textContent=i+1;b.onclick=()=>{show(i);closePal()};g.appendChild(b)}
  p.appendChild(g);
 });
 refresh();
}
function counts(){const c={an:0,na:0,nv:0,mr:0,am:0};Q.forEach((q,i)=>{const k=st(i)||"nv";c[k]++});return c}
function refresh(){
 Q.forEach((q,i)=>{const b=$("pb"+i);if(b)b.className="b "+st(i)+(i===cur?" cur":"")});
 if(mode==="sol"){
  let c=0,w=0,u=0;Q.forEach((q,i)=>{const a=S[i].a;a===null?u++:a===q[3]?c++:w++});
  $("cnts").innerHTML=`<div><span class="b sm an">${c}</span>सही</div><div><span class="b sm na">${w}</span>गलत</div><div class="w"><span class="b sm">${u}</span>छोड़े गए</div>`;return;
 }
 const c=counts();
 $("cnts").innerHTML=`<div><span class="b sm an">${c.an}</span>Answered</div><div><span class="b sm na">${c.na}</span>Not Answered</div><div><span class="b sm">${c.nv}</span>Not Visited</div><div><span class="b sm mr">${c.mr}</span>Marked</div><div class="w"><span class="b sm am">${c.am}</span>Answered &amp; Marked</div>`;
}
function show(i){
 cur=i;const q=Q[i],s=S[i];
 if(mode==="test"){s.v=true;s.sel=s.a}
 $("qn").textContent=`Q.${i+1}`;$("qtext").innerHTML=fmt(q[1]);
 const o=$("opts");o.innerHTML="";
 q[2].forEach((t,k)=>{
  const b=document.createElement("button");let cls="op",extra="";
  if(mode==="test"){if(s.sel===k)cls+=" on";b.onclick=()=>{s.sel=k;[...o.children].forEach((c,j)=>c.classList.toggle("on",j===k))}}
  else{if(k===q[3]){cls+=" ok";extra=`<span class="yt" style="color:var(--gr)">सही उत्तर${s.a===k?" • आपका उत्तर":""}</span>`}else if(s.a===k){cls+=" bad";extra=`<span class="yt" style="color:var(--rd)">आपका उत्तर</span>`}}
  b.className=cls;b.innerHTML=`<span class="l">${L[k]}</span><span>${t}${extra}</span>`;o.appendChild(b);
 });
 $("qscroll").scrollTop=0;
 if(mode==="test"){$("bPrev").disabled=i===0;$("qt").innerHTML=clockIco()+mmss(s.t);syncSec()}
 else{const pos=order.indexOf(i);$("sPrev").disabled=pos<=0;$("sNext").textContent=pos>=order.length-1?"खत्म":"Next";
  const a=s.a;$("qt").innerHTML=(a===null?'<span style="color:var(--mu)">छोड़ा गया</span>':a===q[3]?'<span style="color:var(--gr);font-weight:600">सही</span>':'<span style="color:var(--rd);font-weight:600">गलत</span>')+` • ${clockIco()}${mmss(s.t)}`}
 refresh();
}
function next(){if(cur<Q.length-1)show(cur+1);else{refresh();openPal()}}
$("bSave").onclick=()=>{S[cur].a=S[cur].sel;S[cur].m=false;next()};
$("bMark").onclick=()=>{S[cur].a=S[cur].sel;S[cur].m=true;next()};
$("bClear").onclick=()=>{S[cur].a=null;S[cur].sel=null;S[cur].m=false;show(cur)};
$("bPrev").onclick=()=>{if(cur>0)show(cur-1)};
$("sPrev").onclick=()=>{const p=order.indexOf(cur);if(p>0)show(order[p-1])};
$("sNext").onclick=()=>{const p=order.indexOf(cur);if(p<order.length-1)show(order[p+1]);else showResult()};
function openPal(){$("side").classList.add("open");$("scrim").classList.remove("hide")}
function closePal(){$("side").classList.remove("open");$("scrim").classList.add("hide")}
function closeAll(){closePal();$("dlg").classList.add("hide")}
function askSubmit(){
 closePal();let rows="";
 secList().forEach(x=>{const c={an:0,na:0,nv:0,mr:0};for(let i=x.f;i<x.f+x.c;i++){const k=st(i);if(k==="an"||k==="am")c.an++;else if(k==="mr")c.mr++;else if(k==="na")c.na++;else c.nv++}
  rows+=`<tr><td>${sname(x.n)}</td><td>${c.an}</td><td>${c.na}</td><td>${c.mr}</td><td>${c.nv}</td></tr>`});
 $("dlg").innerHTML=`<h3>टेस्ट सबमिट करें?</h3><p>बचा समय : ${mmss(Math.max(left,0))}</p><table class="stab"><tr><th>Section</th><th>Answered</th><th>Not Ans.</th><th>Marked</th><th>Not Visited</th></tr>${rows}</table><div class="dact"><button class="a" onclick="closeAll()">Resume</button><button class="b2" onclick="closeAll();finish()">Submit</button></div>`;
 $("dlg").classList.remove("hide");$("scrim").classList.remove("hide");
}
function finish(){
 if(mode!=="test")return;clearInterval(tick);closeAll();mode="done";
 const r=calc();store("stet_res_"+mk,{score:r.score});showResult();
}
function calc(){let c=0,w=0,u=0,t=0;const secs=[];
 secList().forEach(x=>{const s={n:x.n,q:x.c,c:0,w:0,u:0,t:0};for(let i=x.f;i<x.f+x.c;i++){const a=S[i].a;s.t+=S[i].t;a===null?s.u++:a===Q[i][3]?s.c++:s.w++}secs.push(s);c+=s.c;w+=s.w;u+=s.u;t+=s.t});
 return {c,w,u,t,secs,score:c*3-w,used:TOTAL-Math.max(left,0)}}
function showResult(){
 mode="done";closeAll();$("vApp").classList.add("hide");
 const r=calc(),acc=r.c+r.w?Math.round(r.c*100/(r.c+r.w)):0,pct=Math.max(0,r.score)/360,R=46,C=2*Math.PI*R;
 let secs="";r.secs.forEach(s=>{secs+=`<div class="srow"><div class="h">${s.n}<span>${s.c*3-s.w} / ${s.q*3}</span></div><div class="d"><span style="color:var(--gr)">सही ${s.c}</span><span style="color:var(--rd)">गलत ${s.w}</span><span>छोड़े ${s.u}</span><span>समय ${mmss(s.t)}</span></div></div>`});
 $("vRes").innerHTML=`<div class="rhead"><small>Exam Hub • Result</small><h2>STET Mock Test ${mk}</h2></div>
 <div class="card top"><div class="scorebox"><div class="ring"><svg width="108" height="108" viewBox="0 0 108 108"><circle cx="54" cy="54" r="${R}" fill="none" stroke="#eef1f5" stroke-width="10"/><circle cx="54" cy="54" r="${R}" fill="none" stroke="#1a73e8" stroke-width="10" stroke-linecap="round" stroke-dasharray="${C}" stroke-dashoffset="${C*(1-pct)}" transform="rotate(-90 54 54)"/></svg><div class="c"><b>${r.score}</b><span>/ 360</span></div></div>
 <div class="sline"><div><b>${r.c+r.w}/120</b>Attempted</div><div><b>${acc}%</b>Accuracy</div><div><b>${mmss(r.used)}</b>समय लगा</div><div><b>${(r.score/3.6).toFixed(1)}%</b>प्रतिशत</div></div></div></div>
 <div class="card"><div class="ctitle">उत्तरों का सार</div><div class="bars"><i style="width:${r.c/1.2}%;background:var(--gr)"></i><i style="width:${r.w/1.2}%;background:var(--rd)"></i><i style="width:${r.u/1.2}%;background:#c9d1dc"></i></div>
 <div class="blg"><span style="--c:var(--gr)">सही ${r.c}</span><span style="--c:var(--rd)">गलत ${r.w}</span><span style="--c:#c9d1dc">छोड़े ${r.u}</span></div></div>
 <div class="card"><div class="ctitle">Section-wise Analysis</div>${secs}</div>
 <div class="ract"><button class="p1" onclick="openSol('all')">Solutions देखें</button><button class="p2" onclick="goLand()">दूसरा टेस्ट</button></div>
 <div class="foot">Exam Hub • Telegram : @examhub2026</div>`;
 $("vRes").classList.remove("hide");window.scrollTo(0,0);
}
function openSol(f){
 mode="sol";filt=f;$("vRes").classList.add("hide");$("vApp").classList.remove("hide");
 $("hBtn").textContent="Result";$("hBtn").onclick=showResult;$("clock").classList.add("hide");
 $("secs").classList.add("hide");$("fchips").classList.remove("hide");$("ftrT").classList.add("hide");$("ftrS").classList.remove("hide");$("sft").classList.add("hide");
 $("hSub").textContent="Solutions";
 const r=calc(),fs=[["all","सभी",120],["w","गलत",r.w],["c","सही",r.c],["u","छोड़े",r.u]];
 $("fchips").innerHTML=fs.map(x=>`<button class="chip${x[0]===f?" on":""}" onclick="openSol('${x[0]}')">${x[1]} (${x[2]})</button>`).join("");
 order=Q.map((q,i)=>i).filter(i=>{const a=S[i].a,t=a===null?"u":a===Q[i][3]?"c":"w";return f==="all"||f===t});
 buildPal();
 if(!order.length){$("qn").textContent="";$("qtext").innerHTML="<div style='color:var(--mu);padding:30px 0;text-align:center'>इस सूची में कोई प्रश्न नहीं है।</div>";$("opts").innerHTML="";$("qt").innerHTML="";return}
 show(order[0]);
}
drawLand();
</script>
</body>
</html>
