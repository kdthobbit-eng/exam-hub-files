"""Publish telegraph/<slug>.json as Telegraph (Instant View) pages; writes <slug>.urls.txt."""
import json, sys, urllib.parse, urllib.request

def api(method, **p):
    data = urllib.parse.urlencode({k: json.dumps(v, ensure_ascii=False) if isinstance(v, (list, dict)) else v for k, v in p.items()}).encode()
    with urllib.request.urlopen("https://api.telegra.ph/" + method, data=data, timeout=60) as r:
        res = json.loads(r.read())
    if not res.get("ok"):
        raise RuntimeError(f"{method}: {res}")
    return res["result"]

path = sys.argv[1]
d = json.load(open(path, encoding="utf-8"))
tok = api("createAccount", short_name="ExamHub", author_name=d["author"], author_url=d["author_url"])["access_token"]
if "sections" in d:
    lines = []
    for sec in d["sections"]:
        r = api("createPage", access_token=tok, title=f"{d['title']} — {sec['name']}", author_name=d["author"],
                author_url=d["author_url"], content=sec["content"])
        lines.append(f"{sec['name']}\t{r['url']}")
        print(f"::notice::{sec['name']} {r['url']}")
    open(path.replace(".json", ".urls.txt"), "w").write("\n".join(lines) + "\n")
    sys.exit(0)
pages, n, urls, nxt = d["pages"], len(d["pages"]), [], None
for i in range(n - 1, -1, -1):
    content = list(pages[i])
    if nxt:
        content.append({"tag": "h4", "children": [{"tag": "a", "attrs": {"href": nxt}, "children": [f"👉 अगला भाग ({i + 2}/{n}) — यहाँ क्लिक करें"]}]})
    title = d["title"] + (f" (भाग {i + 1}/{n})" if n > 1 else "")
    r = api("createPage", access_token=tok, title=title, author_name=d["author"], author_url=d["author_url"], content=content)
    nxt = r["url"]
    urls.insert(0, r["url"])
open(path.replace(".json", ".urls.txt"), "w").write("\n".join(urls) + "\n")
for u in urls:
    print(f"::notice::{u}")
