import base64
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
h=f"""<meta charset=utf-8><style>@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1080px;height:1350px;font-family:B;font-weight:700;background:#fff4d6;overflow:hidden;position:relative}}
.top{{position:absolute;left:0;right:0;top:0;height:740px;background:linear-gradient(135deg,#1a237e,#4a148c);padding:80px 60px 0}}
.a{{color:#fff;font-size:182px;line-height:1.05;white-space:nowrap}}
.b{{color:#ffd54f;font-size:150px;line-height:1.12;margin-top:20px}}
.bot{{position:absolute;left:60px;right:40px;top:780px}}
.c{{color:#b71c1c;font-size:124px;line-height:1.05}}
.d{{color:#1a237e;font-size:100px;line-height:1.1;margin-top:22px}}
</style>
<div class=top><div class=a>UP PET 2026</div><div class=b>30 अक्टूबर<br>पहली पाली</div></div>
<div class=bot><div class=c>Solved First<br>Shift Paper</div><div class=d>Telegram Quiz में<br>Attempt करें</div></div>"""
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1080,"height":1350},device_scale_factor=2)
    pg.set_content(h); pg.wait_for_timeout(300); pg.screenshot(path="/home/claude/exam-hub-files/quiz/img/pet-30oct-s1-quiz.jpg",type="jpeg",quality=95)
    b.close()
