"""
One command turns a solved paper (meta.json + questions.json) into:

  out/<slug>-solution.pdf    Hindi solution PDF: answer key grid + every question with
                             options, answer, explanation and source, Exam Hub branded
  out/<slug>-attempt.html    stand-alone mock test (timer, palette, -marking, result,
                             explanations) to share on Telegram
  out/<slug>.inbox.json      publishes the mock test (+ the PDFs) to the app and website

  python3 build.py work/pet-25-10-s1

questions.json: [{"n":1, "section":"इतिहास", "q":"…", "options":["…","…","…","…"],
                  "answer":1, "explain":"…", "source":"…", "doubt":false}, …]
meta.json:      {"title":"…", "exam":"UPSSSC", "subject":"PET 2026", "minutes":120,
                 "negative":0.25, "date":"25 अक्टूबर 2026", "shift":"प्रथम पाली",
                 "note":"संभावित उत्तर — आधिकारिक उत्तर कुंजी आने पर मिलान करें"}
"""
import base64
import html
import json
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import brand_pdf  # noqa: E402

LETTERS = "ABCDE"
LINKS = {
    "telegram": "https://t.me/examhub2026",
    "app": "https://play.google.com/store/apps/details?id=com.upboardprep",
    "web": "https://erojgarresult.com/exam-hub/",
}


def e(t):
    return html.escape(str(t or ""))


def font_faces():
    css = []
    for w in (400, 600, 700):
        for sub in ("devanagari", "latin"):
            p = os.path.join(HERE, "fonts", f"mukta-{sub}-{w}-normal.woff2")
            if os.path.exists(p):
                b = base64.b64encode(open(p, "rb").read()).decode()
                css.append(f"@font-face{{font-family:Mukta;font-weight:{w};src:url(data:font/woff2;base64,{b}) format('woff2')}}")
    return "\n".join(css)


def logo_data():
    p = os.path.join(HERE, "logo.png")
    return "data:image/png;base64," + base64.b64encode(open(p, "rb").read()).decode()


def validate(qs):
    problems = []
    for i, q in enumerate(qs, 1):
        if len(q.get("options", [])) not in (4, 5):
            problems.append(f"Q{i}: 4 vikalp nahi")
        if q.get("answer") not in range(len(q.get("options", []))):
            problems.append(f"Q{i}: answer 0-3 hona chahiye")
        if not q.get("q"):
            problems.append(f"Q{i}: prashn khali")
    return problems


# ------------------------------------------------------------------ solution PDF

def img_data(path, base):
    if not path:
        return ""
    fp = path if os.path.isabs(path) else os.path.join(base, path)
    ext = os.path.splitext(fp)[1].lstrip(".").lower().replace("jpg", "jpeg") or "png"
    return f"data:image/{ext};base64," + base64.b64encode(open(fp, "rb").read()).decode()


def src_html(q):
    """स्रोत line with a clickable official link (source_url)."""
    if not q.get("source"):
        return ""
    u = q.get("source_url")
    link = f' — <a href="{e(u)}">{e(u.split("//")[-1].rstrip("/")[:60])}</a>' if u else ""
    return f'<p class="src"><b>स्रोत (Official):</b> {e(q["source"])}{link}</p>'


def solution_html(meta, qs, base="."):
    key = "".join(
        f'<div class="k{" d" if q.get("doubt") else ""}"><b>{q.get("n", i + 1)}</b>{LETTERS[q["answer"]]}</div>'
        for i, q in enumerate(qs))
    cards = []
    prev_ctx = None
    for i, q in enumerate(qs):
        n = q.get("n", i + 1)
        key_ctx = (q.get("ctx"), q.get("ctx_img"))
        if (q.get("ctx") or q.get("ctx_img")) and key_ctx != prev_ctx:
            im = f'<img class="ctximg" src="{img_data(q["ctx_img"], base)}">' if q.get("ctx_img") else ""
            cards.append(f'<div class="ctx">{im}{e(q.get("ctx", "")) if not q.get("ctx_img") else ""}</div>')
        prev_ctx = key_ctx
        opts = "".join(
            f'<li class="{"ok" if j == q["answer"] else ""}"><span>{LETTERS[j]}</span>{e(o)}</li>'
            for j, o in enumerate(q["options"]))
        cards.append(f'''<section class="q">
  <div class="qh"><span class="no">प्रश्न {n}</span>{f'<span class="sec">{e(q.get("section"))}</span>' if q.get("section") else ""}{'<span class="doubt">पुनः जाँचें</span>' if q.get("doubt") else ""}</div>
  <p class="qt">{e(q["q"])}</p>
  <ol class="op">{opts}</ol>
  <p class="ans">✔ सही उत्तर: ({LETTERS[q["answer"]]}) {e(q["options"][q["answer"]])}</p>
  {f'<p class="ex"><b>व्याख्या:</b> {e(q.get("explain"))}</p>' if q.get("explain") else ""}
  {src_html(q)}
</section>''')
    return f'''<!doctype html><html lang="hi"><head><meta charset="utf-8"><title>{e(meta["title"])}</title><style>
{font_faces()}
@page{{size:A4;margin:14mm 13mm 14mm 13mm}}
*{{box-sizing:border-box}}
body{{font-family:Mukta,sans-serif;color:#13201c;font-size:11.2pt;line-height:1.45;margin:0}}
.cover{{border:2px solid #015a3e;border-radius:14px;padding:16px 18px;margin-bottom:14px;display:flex;gap:16px;align-items:center}}
.cover img{{width:74px;height:74px}}
.cover h1{{font-size:20pt;margin:0;color:#015a3e;line-height:1.15}}
.cover p{{margin:4px 0 0;color:#4d5c57}}
.meta{{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}}
.meta span{{background:#e0efe9;color:#015a3e;border-radius:99px;padding:2px 10px;font-size:9.5pt;font-weight:600}}
.note{{background:#fdf0e1;border-left:4px solid #ec8516;padding:8px 12px;border-radius:8px;font-size:10pt;margin-bottom:12px}}
h2{{font-size:13pt;color:#015a3e;margin:6px 0 8px}}
.key{{display:grid;grid-template-columns:repeat(10,1fr);gap:4px;margin-bottom:16px}}
.k{{border:1px solid #cfdcd7;border-radius:6px;text-align:center;font-size:9.5pt;padding:2px 0;font-weight:700}}
.k b{{display:block;font-weight:400;color:#6a7a75;font-size:8pt}}
.k.d{{border-color:#ec8516;background:#fdf0e1}}
.q{{break-inside:avoid;border:1px solid #dfe7e4;border-radius:10px;padding:9px 12px;margin-bottom:9px}}
.qh{{display:flex;gap:6px;align-items:center;margin-bottom:3px}}
.no{{font-weight:700;color:#015a3e}}
.sec{{background:#eef2f0;border-radius:99px;padding:0 8px;font-size:8.5pt;color:#4d5c57}}
.doubt{{background:#fdf0e1;color:#b35d00;border-radius:99px;padding:0 8px;font-size:8.5pt;font-weight:700}}
.qt{{margin:0 0 4px;font-weight:600}}
.op{{list-style:none;margin:0 0 4px;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:2px 14px}}
.op li{{display:flex;gap:6px}}
.op li span{{font-weight:700;color:#6a7a75}}
.op li.ok{{color:#0d7a3e;font-weight:700}}
.op li.ok span{{color:#0d7a3e}}
.ans{{margin:2px 0;color:#0d7a3e;font-weight:700}}
.ex{{margin:2px 0;white-space:pre-line}}
.ctx{{background:#f4f8f6;border:1.5px dashed #015a3e;border-radius:10px;padding:9px 12px;margin:4px 0 9px;white-space:pre-line;font-size:10.5pt;break-inside:avoid}}
.src{{margin:2px 0;color:#4d5c57;font-size:10pt}}
.src a{{color:#0b57d0;text-decoration:underline}}
.ctximg{{display:block;max-width:100%;max-height:120mm;margin:0 auto}}
</style></head><body>
<div class="cover"><img src="{logo_data()}" alt=""><div><h1>{e(meta["title"])}</h1>
<p>{e(meta.get("subtitle", "Unofficial Answer Key · विस्तृत हल · सारे Source Official Website से (Link सहित)"))}</p>
<div class="meta"><span>{e(meta.get("date"))}</span><span>{e(meta.get("shift"))}</span><span>{len(qs)} प्रश्न</span><span>{meta.get("minutes", 120)} मिनट</span><span>+1 / −{meta.get("negative", 0.25)}</span></div></div></div>
{f'<div class="note">{e(meta.get("note"))}</div>' if meta.get("note") else ""}
<h2>उत्तर कुंजी</h2><div class="key">{key}</div>
<h2>प्रश्नवार हल</h2>
{"".join(cards)}
</body></html>'''


def html_to_pdf(html_path, pdf_path):
    from playwright.sync_api import sync_playwright
    with sync_playwright() as p:
        b = p.chromium.launch()
        pg = b.new_page()
        pg.goto("file://" + os.path.abspath(html_path))
        pg.wait_for_timeout(400)
        pg.pdf(path=pdf_path, format="A4", print_background=True,
               margin={"top": "12mm", "bottom": "12mm", "left": "12mm", "right": "12mm"})
        b.close()


# ------------------------------------------------------------------ attempt HTML

def attempt_html(meta, qs, base="."):
    qs = [dict(q, ctx_img=img_data(q.get("ctx_img"), base)) if q.get("ctx_img") else q for q in qs]
    data = json.dumps({"meta": meta, "qs": qs}, ensure_ascii=False)
    return f'''<!doctype html><html lang="hi"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title>{e(meta["title"])} – Mock Test | Exam Hub</title><style>
{font_faces()}
:root{{--b:#015a3e;--bs:#e0efe9;--a:#ec8516;--ok:#1d8a4b;--bad:#c9372c;--bg:#f2f5f4;--s:#fff;--l:#dfe7e4;--m:#5f6e6a;--ink:#13201c}}
*{{box-sizing:border-box}}body{{margin:0;font:16px/1.5 Mukta,sans-serif;background:var(--bg);color:var(--ink)}}
button,a{{font:inherit;cursor:pointer}}button{{border:0;background:none;color:inherit}}a{{color:inherit;text-decoration:none}}
.top{{background:var(--b);color:#fff;padding:10px 14px;display:flex;align-items:center;gap:10px;position:sticky;top:0;z-index:5}}
.top img{{width:34px;height:34px;border-radius:9px;background:#fff}}.top b{{flex:1;font-weight:700}}
.timer{{font-weight:700;font-size:20px;color:#ffd29e;font-variant-numeric:tabular-nums}}
.wrap{{max-width:980px;margin:0 auto;padding:14px}}
.links{{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}}.links a{{background:var(--s);border:1px solid var(--l);border-radius:99px;padding:5px 12px;font-size:14px;font-weight:600;color:var(--b)}}
.card{{background:var(--s);border:1px solid var(--l);border-radius:16px;padding:16px;margin-bottom:12px}}
.lay{{display:grid;grid-template-columns:1fr 270px;gap:14px;align-items:start}}@media(max-width:800px){{.lay{{grid-template-columns:1fr}}}}
.q{{font-size:19px;font-weight:600;margin:6px 0 12px}}
.opt{{display:flex;gap:10px;align-items:center;width:100%;text-align:left;padding:12px;border:1.5px solid var(--l);border-radius:12px;background:var(--s);margin-bottom:8px}}
.opt .k{{width:28px;height:28px;border-radius:8px;background:#eef2f0;display:grid;place-items:center;font-weight:700;flex:none}}
.opt.sel{{border-color:var(--b);background:var(--bs)}}.opt.sel .k{{background:var(--b);color:#fff}}
.opt.r{{border-color:var(--ok);background:#e6f4ec}}.opt.w{{border-color:var(--bad);background:#fbeceb}}
.pal{{display:flex;flex-wrap:wrap;gap:5px}}.pal button{{width:34px;height:34px;border-radius:9px;border:1.5px solid var(--l);font-weight:700;background:var(--s);font-size:13px}}
.pal .a{{background:var(--b);border-color:var(--b);color:#fff}}.pal .c{{outline:2px solid var(--a)}}
.btn{{background:var(--b);color:#fff;border-radius:12px;padding:11px 16px;font-weight:700}}.btn.alt{{background:var(--a)}}.btn.g{{background:var(--s);color:var(--ink);border:1.5px solid var(--l)}}
.row{{display:flex;gap:8px;flex-wrap:wrap;align-items:center}}
.stats{{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center}}.stats div{{background:#eef2f0;border-radius:12px;padding:8px}}.stats b{{display:block;font-size:22px}}
.ex{{background:var(--bs);border-radius:10px;padding:8px 10px;margin-top:6px;font-size:15px}}.src{{color:var(--m);font-size:14px}}
.ctx{{background:#f4f8f6;border:1.5px dashed var(--b);border-radius:12px;padding:10px 12px;white-space:pre-line;font-size:15px;max-height:300px;overflow:auto}}.ex{{white-space:pre-line}}
.intro h1{{margin:0 0 6px;font-size:24px;color:var(--b)}}
</style></head><body>
<div class="top"><img src="{logo_data()}" alt=""><b>Exam Hub 2026</b><span class="timer" id="tm"></span></div>
<div class="wrap" id="app"></div>
<script>
const D={data};
const L="ABCDE",Q=D.qs,M=D.meta,NEG=+M.negative||0;let ans={{}},cur=0,left=(M.minutes||Q.length)*60,tmr=null,done=false;
const $=s=>document.querySelector(s),esc=t=>String(t??"").replace(/[&<>"]/g,c=>({{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}}[c]));
const links=`<div class="links"><a href="{LINKS["telegram"]}" target="_blank">✈️ Telegram</a><a href="{LINKS["web"]}" target="_blank">🌐 Website par rank dekhein</a></div>`;
const mmss=s=>String(Math.floor(Math.max(0,s)/60)).padStart(2,"0")+":"+String(Math.max(0,s)%60).padStart(2,"0");
function intro(){{$("#app").innerHTML=`<div class="card intro"><h1>${{esc(M.title)}}</h1><p>${{Q.length}} प्रश्न · ${{M.minutes||Q.length}} मिनट · सही +1 · गलत −${{NEG}}</p>${{M.note?`<p style="color:#b35d00">${{esc(M.note)}}</p>`:""}}<button class="btn" onclick="start()">टेस्ट शुरू करें</button></div>${{links}}`;}}
function start(){{tmr=setInterval(()=>{{left--;$("#tm").textContent="⏱ "+mmss(left);if(left<=0)submit()}},1000);$("#tm").textContent="⏱ "+mmss(left);draw();}}
function draw(){{const q=Q[cur];$("#app").innerHTML=`<div class="lay"><div class="card"><div class="row"><b style="flex:1">प्रश्न ${{cur+1}} / ${{Q.length}}</b>${{q.section?`<span class="src">${{esc(q.section)}}</span>`:""}}</div>
${{q.ctx_img?`<img src="${{q.ctx_img}}" style="max-width:100%;border-radius:10px;margin-bottom:8px">`:(q.ctx?`<div class="ctx">${{esc(q.ctx)}}</div>`:"")}}<p class="q">${{esc(q.q)}}</p>${{q.options.map((o,i)=>`<button class="opt ${{ans[cur]===i?"sel":""}}" onclick="pick(${{i}})"><span class="k">${{L[i]}}</span>${{esc(o)}}</button>`).join("")}}
<div class="row"><button class="btn g" onclick="mv(-1)">← पिछला</button><button class="btn" onclick="mv(1)">${{cur<Q.length-1?"अगला →":"Submit"}}</button></div></div>
<div class="card"><b>Question palette</b><div class="pal" style="margin:8px 0">${{Q.map((_,i)=>`<button class="${{ans[i]!==undefined?"a":""}} ${{i===cur?"c":""}}" onclick="cur=${{i}};draw()">${{i+1}}</button>`).join("")}}</div>
<p class="src">${{Object.keys(ans).length}} उत्तर दिए · ${{Q.length-Object.keys(ans).length}} बाकी</p><button class="btn alt" style="width:100%" onclick="if(confirm('Test submit karein?'))submit()">Submit</button></div></div>`;}}
function pick(i){{if(ans[cur]===i)delete ans[cur];else ans[cur]=i;draw();}}
function mv(d){{if(d>0&&cur===Q.length-1){{if(confirm('Test submit karein?'))submit();return}}cur=Math.min(Q.length-1,Math.max(0,cur+d));draw();}}
function submit(){{if(done)return;done=true;clearInterval(tmr);let r=0,w=0;Q.forEach((q,i)=>{{if(ans[i]===undefined)return;ans[i]===q.answer?r++:w++}});const sc=r-w*NEG;
$("#tm").textContent="";$("#app").innerHTML=`<div class="card" style="text-align:center"><h2 style="margin:0">${{(+sc.toFixed(2))}} / ${{Q.length}}</h2><p>${{esc(M.title)}}</p>
<div class="stats"><div><b style="color:var(--ok)">${{r}}</b>सही</div><div><b style="color:var(--bad)">${{w}}</b>गलत</div><div><b>${{Q.length-r-w}}</b>छोड़े</div><div><b>${{mmss((M.minutes||Q.length)*60-left)}}</b>समय</div></div>
<p style="margin-top:10px">All-UP rank ke liye yahi test <a href="{LINKS["web"]}" style="color:var(--b);font-weight:700">Exam Hub website</a> ya app me dein.</p></div>${{links}}
${{Q.map((q,i)=>{{const a=ans[i];return `<div class="card"><b>प्रश्न ${{i+1}}. ${{esc(q.q)}}</b><div style="margin-top:8px">${{q.options.map((o,j)=>`<div class="opt ${{j===q.answer?"r":(j===a?"w":"")}}"><span class="k">${{L[j]}}</span>${{esc(o)}}</div>`).join("")}}</div>
${{q.explain?`<div class="ex"><b>व्याख्या:</b> ${{esc(q.explain)}}</div>`:""}}${{q.source?`<p class="src"><b>स्रोत (Official):</b> ${{esc(q.source)}}${{q.source_url?` — <a href="${{esc(q.source_url)}}" target="_blank" style="color:#0b57d0">Link</a>`:""}}</p>`:""}}</div>`}}).join("")}}`;scrollTo(0,0);}}
intro();
</script></body></html>'''


# ------------------------------------------------------------------ main

def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-") or "paper"


def main(work):
    meta = json.load(open(os.path.join(work, "meta.json"), encoding="utf-8"))
    qs = json.load(open(os.path.join(work, "questions.json"), encoding="utf-8"))
    probs = validate(qs)
    if probs:
        print("Galtiyan:\n" + "\n".join(probs))
        sys.exit(1)
    slug = meta.get("slug") or slugify(os.path.basename(os.path.normpath(work)))
    out = os.path.join(work, "out")
    os.makedirs(out, exist_ok=True)

    sol_html = os.path.join(out, f"{slug}-solution.html")
    open(sol_html, "w", encoding="utf-8").write(solution_html(meta, qs, work))
    raw_pdf = os.path.join(out, f"_{slug}-solution-raw.pdf")
    html_to_pdf(sol_html, raw_pdf)
    sol_pdf = os.path.join(out, f"{slug}-solution.pdf")
    brand_pdf.brand(sol_pdf, [raw_pdf])
    os.remove(raw_pdf)

    att = os.path.join(out, f"{slug}-attempt.html")
    open(att, "w", encoding="utf-8").write(attempt_html(meta, qs, work))

    test_text = "\n\n".join(
        f'{i + 1}. {q["q"]}\n' + "\n".join(f"{LETTERS[j]}) {o}" for j, o in enumerate(q["options"]))
        + f'\nउत्तर: {LETTERS[q["answer"]]}'
        + (f'\nव्याख्या: {q.get("explain", "")}' + (f' (स्रोत: {q["source"]})' if q.get("source") else "")
           if q.get("explain") or q.get("source") else "")
        for i, q in enumerate(qs))
    inbox = [{"kind": "test", "exam": meta.get("exam", "UPSSSC"), "subject": meta.get("subject", "PET 2026"),
              "chapter": meta.get("chapter", "हल प्रश्नपत्र"), "title": meta["title"] + " – Mock Test",
              "minutes": meta.get("minutes", 120), "negative": meta.get("negative", 0.25), "text": test_text,
              "questions": [{"q": (q["ctx"] + "\n\n" if q.get("ctx") else "") + q["q"],
                             "q_hi": (q["ctx"] + "\n\n" if q.get("ctx") else "") + q["q"],
                             "options": q["options"], "options_hi": q["options"], "answer": q["answer"],
                             "explain": q.get("explain", "") + (f"\nस्रोत: {q['source']}" if q.get("source") else ""),
                             "explain_hi": q.get("explain", "") + (f"\nस्रोत: {q['source']}" if q.get("source") else "")}
                            for q in qs]}]
    json.dump(inbox, open(os.path.join(out, f"{slug}.inbox.json"), "w", encoding="utf-8"), ensure_ascii=False, indent=1)
    print("\n".join([sol_pdf, att, os.path.join(out, f"{slug}.inbox.json")]))


if __name__ == "__main__":
    main(sys.argv[1] if len(sys.argv) > 1 else ".")
