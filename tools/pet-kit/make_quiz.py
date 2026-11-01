"""questions.json -> quiz/<slug>.json for bot/send_quiz.py (Telegram quiz polls).
   python3 make_quiz.py work/<dir> "PET 25 Oct 2026 II Shift" /home/claude/exam-hub-files
Copies DI pictures (ctx_img) into the files repo and sends them as photos before their questions."""
import json, os, re, shutil, sys

work, tag, repo = sys.argv[1], sys.argv[2], sys.argv[3]
meta = json.load(open(os.path.join(work, "meta.json"), encoding="utf-8"))
qs = json.load(open(os.path.join(work, "questions.json"), encoding="utf-8"))
slug = meta["slug"]
RAW = "https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/main/"


def short(t):
    t = re.sub(r"\s*\n\s*", " ; ", (t or "").strip())
    if len(t) <= 195:
        return t
    c = t[:195]
    i = max(c.rfind("।"), c.rfind(". "), c.rfind(" ; "))
    return (c[:i + 1] if i > 80 else c[:192] + "…").strip(" ;")


esc = lambda s: s.replace("&", "&amp;").replace("<", "&lt;")
items = [{"type": "msg", "text": f"<b>📝 {esc(tag)} — पूरा पेपर Quiz में\n\nउत्तर टिक करें, सही उत्तर व व्याख्या उसके बाद दिखेगी ✅</b>"}]
prev = None
for q in qs:
    key = (q.get("ctx"), q.get("ctx_img"))
    if key != prev and (q.get("ctx") or q.get("ctx_img")):
        if q.get("ctx_img"):
            rel = f"quiz/img/{slug}-{os.path.basename(q['ctx_img'])}"
            os.makedirs(os.path.join(repo, "quiz/img"), exist_ok=True)
            shutil.copy(os.path.join(work, q["ctx_img"]), os.path.join(repo, rel))
            items.append({"type": "photo", "url": RAW + rel, "caption": f"<b>प्रश्न {q['n']} से आगे के लिए ({esc(tag)})</b>"})
        else:
            items.append({"type": "msg", "text": "<b>" + esc(q["ctx"])[:4000] + "</b>"})
    prev = key
    suffix = f" ({tag})"
    qt = f"Q{q['n']}. {q['q']}"
    qt = (qt[:300 - len(suffix) - 1] + "…" if len(qt) + len(suffix) > 300 else qt) + suffix
    opts = [o if len(o) <= 100 else o[:99] + "…" for o in q["options"]]
    items.append({"type": "quiz", "q": qt, "options": opts, "answer": q["answer"], "explain": short(q.get("explain"))})
out = os.path.join(repo, "quiz", f"{slug}.json")
json.dump(items, open(out, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print(out, sum(i["type"] == "quiz" for i in items), "quiz")
