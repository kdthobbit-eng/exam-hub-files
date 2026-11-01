import base64
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
months=["जनवरी","फरवरी","मार्च","अप्रैल","मई","जून","जुलाई","अगस्त","सितंबर"]
h=f"""<meta charset=utf-8><style>@font-face{{font-family:B;src:url({fb('baloo2_medium.ttf')})}}@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1280px;height:720px;font-family:B;background:#fff;overflow:hidden;position:relative}}
.bg{{position:absolute;right:-140px;top:-180px;width:520px;height:520px;border-radius:50%;background:#e0efe9}}
.bg2{{position:absolute;left:-90px;bottom:-120px;width:300px;height:300px;border-radius:50%;background:#fdebd6}}
.brand{{position:absolute;left:56px;top:34px;font-weight:700;font-size:26px;color:#015a3e}}
.pill{{position:absolute;right:56px;top:34px;background:#ec8516;color:#fff;font-weight:700;font-size:22px;padding:6px 22px;border-radius:30px}}
h1{{position:absolute;left:56px;top:100px;margin:0;font-size:64px;line-height:1.15;color:#013d2b}}
h1 span{{color:#ec8516}}
.sub{{position:absolute;left:56px;top:270px;font-size:30px;color:#13201c;width:760px;line-height:1.4}}
.m{{position:absolute;left:56px;top:390px;width:760px;display:flex;flex-wrap:wrap;gap:12px}}
.m div{{background:#e0efe9;color:#015a3e;font-weight:700;font-size:22px;padding:6px 18px;border-radius:24px}}
.key{{position:absolute;left:860px;top:150px;width:370px;height:420px;border-radius:30px;background:#015a3e;padding:34px;box-shadow:0 12px 30px rgba(1,90,62,.3);overflow:hidden;text-align:center}}
.kv{{color:#fff;font-size:150px;font-weight:700;line-height:1}} .kl{{color:#ffdb99;font-size:34px;font-weight:700;margin-top:8px}}
.kt{{color:#e0efe9;font-size:24px;margin-top:22px;line-height:1.4}}
.ring{{position:absolute;right:-70px;bottom:-80px;width:150px;height:150px;border-radius:50%;border:22px solid #ec8516}}
.foot{{position:absolute;left:0;right:0;bottom:0;height:70px;background:#015a3e;color:#fff;font-size:26px;font-weight:700;display:flex;align-items:center;justify-content:space-between;padding:0 56px}}
.foot span{{color:#ffdb99}}</style>
<div class=bg></div><div class=bg2></div><div class=brand>📚 Exam Hub</div><div class=pill>Quiz Live ✅</div>
<h1>करेंट अफेयर्स 2026<br><span>Most Important प्रैक्टिस</span></h1>
<div class=sub>पेपर में जाने से पहले ज़रूर दोहराएँ — सही उत्तर और व्याख्या के साथ</div>
<div class=m>{''.join(f'<div>{x}</div>' for x in months)}</div>
<div class=key><div class=kv>100</div><div class=kl>प्रश्न Quiz</div><div class=kt>अभी Attempt करें<br>👉 @uppetexamhub</div><div class=ring></div></div>
<div class=foot><div>ज्वाइन 👉 <span>@examhub2026</span></div><div>UP PET · UPSSSC · सभी परीक्षाएँ</div></div>"""
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":720},device_scale_factor=2)
    pg.set_content(h); pg.wait_for_timeout(300); pg.screenshot(path="/home/claude/exam-hub-files/quiz/img/ca-jan-sep.jpg",type="jpeg",quality=95)
    b.close()
