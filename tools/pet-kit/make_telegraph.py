"""questions.json -> telegraph/<slug>.json (pages of Telegraph nodes) for bot/telegraph.py"""
import json, os, sys
work, repo, title = sys.argv[1], sys.argv[2], sys.argv[3]
meta = json.load(open(os.path.join(work, "meta.json"), encoding="utf-8"))
qs = json.load(open(os.path.join(work, "questions.json"), encoding="utf-8"))
slug = meta["slug"]
RAW = "https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/main/quiz/img/"
L = "ABCDE"
def a(href, text): return {"tag": "a", "attrs": {"href": href}, "children": [text]}
head = [{"tag": "p", "children": [{"tag": "b", "children": ["✅ Unofficial Answer Key"]}]}]
foot = [{"tag": "p", "children": ["📝 पूरा पेपर Quiz में 👉 ", a("https://t.me/uppetexamhub", "@uppetexamhub"), "  |  ज्वाइन 👉 ", a("https://t.me/examhub2026", "@examhub2026")]}]
key = " ".join(f"{q['n']}-{L[q['answer']]}" for q in qs)
# answer-key list removed (user asked)
blocks, prev = [], None
for q in qs:
    b = []
    k = (q.get("ctx"), q.get("ctx_img"))
    if k != prev and (q.get("ctx") or q.get("ctx_img")):
        if q.get("ctx_img"):
            b.append({"tag": "figure", "children": [{"tag": "img", "attrs": {"src": RAW + f"{slug}-{os.path.basename(q['ctx_img'])}"}}]})
        else:
            b.append({"tag": "blockquote", "children": [q["ctx"]]})
    prev = k
    b.append({"tag": "h4", "children": [f"प्रश्न {q['n']}. " + q["q"].replace("\n", " ")]})
    b.append({"tag": "p", "children": ["\n".join(f"({L[i]}) {o}" for i, o in enumerate(q["options"]))]})
    b.append({"tag": "p", "children": [{"tag": "b", "children": [f"✔ सही उत्तर: ({L[q['answer']]}) {q['options'][q['answer']]}"]}]})
    if q.get("explain"):
        b.append({"tag": "p", "children": [{"tag": "b", "children": ["व्याख्या: "]}, q["explain"]]})
    if q.get("source"):
        s = [{"tag": "i", "children": ["स्रोत (Official): " + q["source"]]}]
        if q.get("source_url"):
            s += [" — ", a(q["source_url"], "Link")]
        b.append({"tag": "p", "children": s})
    blocks.append((q.get("section", "अन्य"), b))
GROUP = {"सामान्य ज्ञान": "करेंट अफेयर्स + GK", "करेंट अफेयर्स": "करेंट अफेयर्स + GK", "अपठित गद्यांश": "हिन्दी गद्यांश",
         "आँकड़ा विश्लेषण": "DI (आँकड़ा विश्लेषण)", "सामान्य हिन्दी": "सामान्य हिन्दी"}
ORDER = ["इतिहास", "करेंट अफेयर्स + GK", "भूगोल", "राजव्यवस्था", "अर्थव्यवस्था", "विज्ञान", "तर्कशक्ति", "गणित",
         "DI (आँकड़ा विश्लेषण)", "सामान्य हिन्दी", "हिन्दी गद्यांश", "General English"]
secs = {}
for sec, b in blocks:
    secs.setdefault(GROUP.get(sec, sec), []).extend(b)
names = sorted(secs, key=lambda k: ORDER.index(k) if k in ORDER else 99)
out = {"title": title, "author": "Exam Hub", "author_url": "https://t.me/examhub2026",
       "sections": [{"name": n, "content": head + secs[n] + foot} for n in names]}
json.dump(out, open(os.path.join(repo, "telegraph", f"{slug}.json"), "w", encoding="utf-8"), ensure_ascii=False)
print([(n, len(json.dumps(secs[n], ensure_ascii=False).encode())) for n in names])
