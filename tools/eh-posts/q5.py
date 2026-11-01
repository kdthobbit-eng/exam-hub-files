import base64
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
h=f"""<meta charset=utf-8><style>@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1080px;height:1350px;font-family:B;font-weight:700;overflow:hidden;position:relative;
background:linear-gradient(160deg,#2b1055 0%,#5b1aa8 45%,#d6336c 100%)}}
.o1{{position:absolute;width:620px;height:620px;border-radius:50%;background:radial-gradient(circle,#ff9a3c 0%,rgba(255,154,60,0) 70%);right:-220px;top:-200px;opacity:.75}}
.o2{{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,#3ec6ff 0%,rgba(62,198,255,0) 70%);left:-220px;top:420px;opacity:.55}}
.top{{position:absolute;left:64px;right:40px;top:70px}}
.a{{color:#fff;font-size:178px;line-height:1.02;white-space:nowrap;letter-spacing:-2px}}
.b{{color:#ffd54f;font-size:132px;line-height:1.08;margin-top:18px}}
.k{{display:inline-block;margin-top:22px;background:rgba(255,255,255,.16);border:3px solid rgba(255,255,255,.45);color:#fff;font-size:62px;padding:6px 34px 0;border-radius:60px}}
.card{{position:absolute;left:48px;right:48px;bottom:44px;height:545px;background:#fff;border-radius:54px;padding:46px 56px;box-shadow:0 30px 60px rgba(0,0,0,.3)}}
.c{{font-size:112px;line-height:1.02;background:linear-gradient(90deg,#5b1aa8,#d6336c);-webkit-background-clip:text;color:transparent}}
.d{{color:#1d1d3a;font-size:84px;line-height:1.08;margin-top:18px}}
.d span{{color:#ff7a00}}
</style>
<div class=o1></div><div class=o2></div>
<div class=top><div class=a>UP PET 2026</div><div class=b>30 अक्टूबर<br>पहली पाली</div><div class=k>(Unofficial Answer Key)</div></div>
<div class=card><div class=c>Solved First<br>Shift Paper</div><div class=d>Telegram Quiz में <span>Attempt करें</span></div></div>"""
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1080,"height":1350},device_scale_factor=2)
    pg.set_content(h); pg.wait_for_timeout(300); pg.screenshot(path="/home/claude/exam-hub-files/quiz/img/pet-30oct-s1-quiz.jpg",type="jpeg",quality=95)
    b.close()
