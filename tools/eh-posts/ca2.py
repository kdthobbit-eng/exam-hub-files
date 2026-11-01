import base64, json, html
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
def short(e):
    p=e.split('।')[0].split(';')[0].strip()
    return p if len(p)<120 else p[:115].rsplit(' ',1)[0]+'…'
css=f"""<style>@font-face{{font-family:B;src:url({fb('baloo2_medium.ttf')})}}@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;font-family:B;background:#eef4f1}} .w{{width:1080px;background:#eef4f1;padding-bottom:0}}
.h{{background:#015a3e;padding:40px 54px 34px;position:relative;overflow:hidden}}
.h .b{{color:#ffdb99;font-size:32px;font-weight:700}}
.h h1{{margin:6px 0 0;color:#fff;font-size:64px;line-height:1.1}} .h h1 span{{color:#ffb35c}}
.h .p{{display:inline-block;margin-top:16px;background:#ec8516;color:#fff;font-size:32px;font-weight:700;padding:4px 24px;border-radius:30px}}
.h .c{{position:absolute;right:-90px;top:-90px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,.07)}}
.cd{{background:#fff;margin:22px 34px;border-radius:26px;padding:24px 30px 22px;display:flex;gap:22px;box-shadow:0 6px 16px rgba(1,90,62,.10)}}
.n{{flex:0 0 66px;height:66px;border-radius:18px;background:#015a3e;color:#fff;font-size:30px;font-weight:700;display:flex;align-items:center;justify-content:center}}
.cd:nth-child(odd) .n{{background:#ec8516}}
.q{{font-size:36px;font-weight:700;color:#13201c;line-height:1.35}}
.a{{display:inline-block;margin-top:12px;background:#d8f3e3;color:#0d5c2f;border:3px solid #1d8a4b;font-size:34px;font-weight:700;padding:2px 20px;border-radius:16px}}
.e{{font-size:27px;color:#4a5752;margin-top:10px;line-height:1.35}}
.f{{background:#ec8516;color:#fff;text-align:center;font-size:38px;font-weight:700;padding:24px;margin-top:26px}} .f span{{color:#013d2b}}</style>"""
jobs=[]
for d,sh,skip in [('pet-30-10-s1','पहली पाली',[]),('pet-30-10-s2','दूसरी पाली',[97])]:
    q=json.load(open(f'/home/claude/pet-kit/work/{d}/questions.json'))
    ca=[x for x in q if x['section']=='करेंट अफेयर्स' and x['n'] not in skip]
    half=(len(ca)+1)//2
    for pi,ch in enumerate([ca[:half],ca[half:]],1):
        cards="".join(f"<div class=cd><div class=n>{x['n']}</div><div><div class=q>{html.escape(x['q'])}</div><div class=a>✔ {html.escape(x['options'][x['answer']])}</div><div class=e>{html.escape(short(x['explain']))}</div></div></div>" for x in ch)
        h=css+f"<div class=w><div class=h><div class=c></div><div class=b>📚 Exam Hub</div><h1>पीईटी में आए<br><span>करेंट अफेयर्स</span></h1><div class=p>30 अक्टूबर 2026 · {sh} · भाग {pi}</div></div><div>{cards}</div><div class=f>पूरा पेपर क्विज़ में 👉 <span>@uppetexamhub</span> · ज्वाइन 👉 <span>@examhub2026</span></div></div>"
        jobs.append((h,f"/home/claude/exam-hub-files/quiz/img/pet30ca-{d[-2:]}-{pi}.jpg"))
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1080,"height":1400},device_scale_factor=2)
    for h,fn in jobs:
        pg.set_content("<meta charset=utf-8>"+h); pg.wait_for_timeout(300); pg.locator(".w").screenshot(path=fn,type="jpeg",quality=92)
    b.close()
print([j[1] for j in jobs])
