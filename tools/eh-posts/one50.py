import base64, json, html
from playwright.sync_api import sync_playwright
fb=lambda n:'data:font/ttf;base64,'+base64.b64encode(open('/home/claude/pet-kit/fonts/'+n,'rb').read()).decode()
L="ABCDE"
def short(e):
    p=e.split('।')[0].split(';')[0].strip()
    return p if len(p)<110 else p[:105].rsplit(' ',1)[0]+'…'
css=f"""<style>@font-face{{font-family:B;src:url({fb('baloo2_medium.ttf')})}}@font-face{{font-family:B;font-weight:700;src:url({fb('baloo2_bold.ttf')})}}
*{{box-sizing:border-box}}body{{margin:0;width:1080px;height:1350px;font-family:B;background:#fff;overflow:hidden;position:relative}}
.top{{position:absolute;left:0;right:0;top:0;height:300px;background:#015a3e;padding:44px 60px}}
.brand{{color:#ffdb99;font-size:36px;font-weight:700}}
.t{{color:#fff;font-size:70px;font-weight:700;line-height:1.1;margin-top:10px}} .t span{{color:#ffb35c}}
.card{{position:absolute;left:44px;right:44px;top:330px;bottom:190px;background:#fff;border-radius:34px;border:4px solid #e0efe9;padding:28px 40px;box-shadow:0 14px 30px rgba(1,90,62,.15);display:flex;flex-direction:column}}
.tag{{align-self:flex-start;background:#ec8516;color:#fff;font-size:30px;font-weight:700;padding:2px 22px;border-radius:30px}}
.q{{font-weight:700;color:#13201c;line-height:1.3;margin:14px 0 16px}}
.o{{font-weight:700;color:#13201c;background:#f2f5f4;border-radius:20px;padding:8px 24px;margin-bottom:12px;line-height:1.3}}
.o b{{color:#015a3e;margin-right:10px}}
.ok{{background:#d8f3e3;border:4px solid #1d8a4b;color:#0d5c2f}}
.e{{margin-top:auto;font-size:28px;color:#4a5752;line-height:1.35;border-top:2px dashed #cfe3da;padding-top:12px}}
.foot{{position:absolute;left:0;right:0;bottom:0;height:170px;background:#ec8516;color:#fff;text-align:center;padding-top:22px}}
.f1{{font-size:44px;font-weight:700}} .f2{{font-size:46px;font-weight:700;color:#013d2b}}</style>"""
out=[]
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1080,"height":1350},device_scale_factor=2)
    sel=json.load(open('/home/claude/eh-posts/sel50.json'))
    for k,(x,sh) in enumerate(sel,1):
        if True:
            idx=[0,1,2,3]+([4] if x['answer']==4 else [])
            for qf,of in [(48,40),(44,36),(40,33),(36,30),(32,28)]:
                oh="".join(f"<div class='o{' ok' if i==x['answer'] else ''}' style='font-size:{of}px'><b>{L[i]}</b>{html.escape(x['options'][i])}{' ✔' if i==x['answer'] else ''}</div>" for i in idx)
                h=css+f"<div class=top><div class=brand>📚 Exam Hub</div><div class=t>पीईटी में ऐसे आ रहे<br><span>करेंट अफेयर्स</span></div></div><div class=card><div class=tag>{sh} · प्रश्न {x['n']}</div><div class=q style='font-size:{qf}px'>{html.escape(x['q'])}</div>{oh}<div class=e>💡 {html.escape(short(x['explain']))}</div></div><div class=foot><div class=f1>ज्वाइन करें 👇</div><div class=f2>चैनल @examhub26 · ग्रुप @examhub2026</div></div>"
                pg.set_content("<meta charset=utf-8>"+h); pg.wait_for_timeout(120)
                over=pg.evaluate("()=>{const c=document.querySelector('.card');return c.scrollHeight>c.clientHeight+2}")
                if not over: break
            fn=f"/home/claude/exam-hub-files/quiz/img/ca50-{k:02d}.jpg"
            pg.screenshot(path=fn,type="jpeg",quality=88); out.append((fn,qf,over))
    b.close()
for o in out: print(o)
