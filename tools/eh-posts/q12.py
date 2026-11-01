import base64
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
months=["जनवरी","फरवरी","मार्च","अप्रैल","मई","जून","जुलाई","अगस्त","सितंबर"]
h=f"""<meta charset=utf-8><style>@font-face{{font-family:B;src:url({fb('baloo2_medium.ttf')})}}@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1080px;height:1350px;font-family:B;background:#fff;overflow:hidden;position:relative}}
.bg{{position:absolute;right:-200px;top:-200px;width:600px;height:600px;border-radius:50%;background:#e0efe9}}
.bg2{{position:absolute;left:-120px;bottom:60px;width:340px;height:340px;border-radius:50%;background:#fdebd6}}
.brand{{position:absolute;left:60px;top:50px;font-weight:700;font-size:44px;color:#015a3e}}
.pill{{position:absolute;right:60px;top:50px;background:#ec8516;color:#fff;font-weight:700;font-size:38px;padding:8px 30px;border-radius:40px}}
h1{{position:absolute;left:60px;top:150px;margin:0;font-size:104px;line-height:1.12;color:#013d2b}}
h1 span{{color:#ec8516}}
.sub{{position:absolute;left:60px;top:560px;font-size:50px;color:#13201c;width:960px;line-height:1.35;font-weight:700}}
.m{{position:absolute;left:60px;top:720px;width:960px;display:flex;flex-wrap:wrap;gap:16px}}
.m div{{background:#e0efe9;color:#015a3e;font-weight:700;font-size:36px;padding:6px 24px;border-radius:30px}}
.key{{position:absolute;left:60px;top:900px;width:960px;height:300px;border-radius:36px;background:#015a3e;padding:30px 50px;box-shadow:0 12px 30px rgba(1,90,62,.3);overflow:hidden;display:flex;align-items:center;gap:40px}}
.kv{{color:#fff;font-size:190px;font-weight:700;line-height:1}} .kl{{color:#ffdb99;font-size:58px;font-weight:700}}
.kt{{color:#fff;font-size:44px;margin-top:10px;line-height:1.3;font-weight:700}}
.ring{{display:none}}
.foot{{position:absolute;left:0;right:0;bottom:0;height:110px;background:#015a3e;color:#fff;font-size:44px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 60px}}
.foot span{{color:#ffdb99}}</style>
<div class=bg></div><div class=bg2></div><div class=brand>📚 Exam Hub</div><div class=pill>क्विज़ लाइव ✅</div>
<h1>बाल विकास एवं<br>शिक्षाशास्त्र<br><span>(CDP)</span></h1>
<div class=sub>UP TET · SUPER TET · CTET के लिए<br>सही उत्तर और व्याख्या के साथ</div>
<div class=m><div>पियाजे</div><div>वायगोत्स्की</div><div>कोलबर्ग</div><div>बुद्धि</div><div>RTE 2009</div></div>
<div class=key><div class=kv>50</div><div><div class=kl>प्रश्न क्विज़</div><div class=kt>अभी हल करें<br>👉 @uppetexamhub</div></div></div>
<div class=foot><div>चैनल 👉 <span>@examhub26</span> · ग्रुप 👉 <span>@examhub2026</span></div></div>"""
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1080,"height":1350},device_scale_factor=2)
    pg.set_content(h); pg.wait_for_timeout(300); pg.screenshot(path="/home/claude/exam-hub-files/quiz/img/cdp-50.jpg",type="jpeg",quality=95)
    b.close()
