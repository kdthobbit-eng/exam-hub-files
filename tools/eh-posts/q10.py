import base64
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
opts=[("A","कैनबरा",0),("B","नैरोबी",0),("C","केप टाउन",1),("D","लंदन",0)]
oh="".join(f"<div class='o{' ok' if c else ''}'><b>{l}</b> {t}{' ✔' if c else ''}</div>" for l,t,c in opts)
h=f"""<meta charset=utf-8><style>@font-face{{font-family:B;src:url({fb('baloo2_medium.ttf')})}}@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1080px;height:1350px;font-family:B;background:#fff;overflow:hidden;position:relative}}
.top{{position:absolute;left:0;right:0;top:0;height:330px;background:#015a3e;padding:50px 60px}}
.brand{{color:#ffdb99;font-size:40px;font-weight:700}}
.t{{color:#fff;font-size:76px;font-weight:700;line-height:1.12;margin-top:14px}}
.t span{{color:#ffb35c}}
.card{{position:absolute;left:50px;right:50px;top:360px;background:#fff;border-radius:34px;border:4px solid #e0efe9;padding:32px 46px;box-shadow:0 14px 30px rgba(1,90,62,.15)}}
.tag{{display:inline-block;background:#ec8516;color:#fff;font-size:34px;font-weight:700;padding:4px 24px;border-radius:30px}}
.q{{font-size:52px;font-weight:700;color:#13201c;line-height:1.3;margin:16px 0 20px}}
.o{{font-size:46px;font-weight:700;color:#13201c;background:#f2f5f4;border-radius:22px;padding:12px 28px;margin-bottom:14px}}
.o b{{color:#015a3e;margin-right:10px}}
.ok{{background:#d8f3e3;border:4px solid #1d8a4b;color:#0d5c2f}}
.foot{{position:absolute;left:0;right:0;bottom:0;height:200px;background:#ec8516;color:#fff;text-align:center;padding-top:30px}}
.f1{{font-size:50px;font-weight:700}} .f2{{font-size:56px;font-weight:700;color:#013d2b;margin-top:4px}}
</style>
<div class=top><div class=brand>📚 Exam Hub</div><div class=t>पीईटी में ऐसे आ रहे<br><span>करेंट अफेयर्स</span></div></div>
<div class=card><span class=tag>30 अक्टूबर · दूसरी पाली</span>
<div class=q>69वाँ राष्ट्रमंडल संसदीय सम्मेलन (CPC) 2026 कहाँ हुआ?</div>{oh}</div>
<div class=foot><div class=f1>इसी पैटर्न पर रोज़ क्विज़ 👇</div><div class=f2>@uppetexamhub</div></div>"""
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1080,"height":1350},device_scale_factor=2)
    pg.set_content(h); pg.wait_for_timeout(300); pg.screenshot(path="/home/claude/exam-hub-files/quiz/img/ca-sample-cpc.jpg",type="jpeg",quality=95)
    b.close()
