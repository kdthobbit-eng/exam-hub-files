"""Build one combined UPPSC PYQ PDF (with a source page before every paper) and send it to Telegram.

Reads pyq/sources.json. Runs on GitHub Actions (open internet). Output: out/UPPSC_PYQ_part*.pdf
Env: TELEGRAM_BOT_TOKEN, CHAT_ID (optional: skip sending if empty), MAX_MB (default 45)
"""
import hashlib, io, json, os, re, sys, time, urllib.parse
import requests
from concurrent.futures import ThreadPoolExecutor
from bs4 import BeautifulSoup
from pypdf import PdfReader, PdfWriter
from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas

UA = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"}
HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(os.getcwd(), "out"); os.makedirs(OUT, exist_ok=True)
MAX = int(os.environ.get("MAX_MB", "45")) * 1024 * 1024
S = requests.Session(); S.headers.update(UA)


BAD = {}  # host -> consecutive failures


def get(url, tries=2):
    host = urllib.parse.urlparse(url).netloc
    if BAD.get(host, 0) >= 4:
        print("  skip (host down)", url); return None
    for i in range(tries):
        try:
            hdr = dict(UA)
            if "uppsc.up.nic.in" in host:
                hdr["Referer"] = "https://uppsc.up.nic.in/OuterPages/PreQuesPapers.aspx?ID=PrevQues"
            r = S.get(url, headers=hdr, timeout=(15, 60), allow_redirects=True)
            if r.status_code == 200:
                BAD[host] = 0
                return r
            print("  http", r.status_code, url)
        except Exception as e:
            print("  retry", i, url, type(e).__name__)
        time.sleep(2)
    BAD[host] = BAD.get(host, 0) + 1
    return None


def scrape(page):
    """Return [(title, pdf_url, source)] from a public page."""
    r = get(page["url"])
    if not r:
        print("PAGE FAIL", page["url"]); return []
    soup = BeautifulSoup(r.text, "html.parser")
    inc = re.compile(page["include"], re.I) if page.get("include") else None
    exc = re.compile(page["exclude"], re.I) if page.get("exclude") else None
    res, seen = [], set()
    official = "uppsc.up.nic.in" in page["url"]
    for a in soup.find_all("a", href=True):
        href = urllib.parse.urljoin(page["url"], a["href"])
        if official:
            if "Upload_QuestionPaper" not in href and "Open_PDF.aspx" not in href:
                continue
        elif not re.search(r"\.pdf($|\?)", href, re.I):
            continue
        row = a.find_parent("tr")
        label = " ".join((row.get_text(" ") if row else a.get_text(" ")).split())
        if official and row:
            cells = [" ".join(td.get_text(" ").split()) for td in row.find_all("td")]
            desc = [c for c in cells if re.search(r"EXAM", c, re.I)]
            label = (desc[-1] if desc else label)
        label = re.sub(r"\b(Download( PDF| Here| Link)?|Click Here|View/Download)\b", "", label, flags=re.I).strip(" |-")[:150] or href.rsplit("/", 1)[-1]
        text = label + " " + href
        if inc and not inc.search(text): continue
        if exc and exc.search(text): continue
        if href in seen: continue
        seen.add(href); res.append((label, href, page.get("source", urllib.parse.urlparse(page["url"]).netloc)))
    print(f"page {page['url']} -> {len(res)} pdfs")
    return res


def sep_page(lines, big=None):
    buf = io.BytesIO(); c = canvas.Canvas(buf, pagesize=A4); w, h = A4
    y = h - 90
    if big:
        c.setFont("Helvetica-Bold", 20); c.drawString(50, y, big[:60]); y -= 40
    c.setFont("Helvetica", 11)
    for ln in lines:
        for chunk in [ln[i:i + 95] for i in range(0, len(ln), 95)] or [""]:
            if y < 50:
                c.showPage(); c.setFont("Helvetica", 11); y = h - 60
            c.drawString(50, y, chunk); y -= 16
    c.showPage(); c.save(); buf.seek(0)
    return PdfReader(buf)


def main():
    cfg = json.load(open(os.path.join(HERE, "sources.json")))
    entries, fails, hashes = [], [], set()
    only = os.environ.get("ONLY", "")
    for sec in cfg["sections"]:
        if only and not re.search(only, sec["name"], re.I):
            continue
        items = [tuple(x) for x in sec.get("items", [])]
        for p in sec.get("pages", []):
            items += scrape(p)
        with ThreadPoolExecutor(8) as ex:
            got = list(ex.map(lambda it: get(it[1]), items))
        for (title, url, src), r in zip(items, got):
            data = r.content if r else b""
            if not data.startswith(b"%PDF"):
                fails.append(f"{title} | {url}"); print("FAIL", title, url); continue
            hsh = hashlib.sha1(data).hexdigest()
            if hsh in hashes:
                continue
            hashes.add(hsh)
            try:
                rd = PdfReader(io.BytesIO(data))
                n = len(rd.pages)
            except Exception as e:
                fails.append(f"{title} | {url} | {e}"); continue
            entries.append({"sec": sec["name"], "title": title, "url": url, "src": src, "data": data, "pages": n})
            print("OK", n, "pp", title, flush=True)

    # split into parts by size
    parts, cur, size = [], [], 0
    for e in entries:
        if cur and size + len(e["data"]) > MAX:
            parts.append(cur); cur, size = [], 0
        cur.append(e); size += len(e["data"])
    if cur: parts.append(cur)

    files = []
    for pi, part in enumerate(parts, 1):
        w = PdfWriter()
        idx = [f"UPPSC PYQ 2015-2026 (Prelims + objective Mains)  -  Part {pi} of {len(parts)}", ""]
        last = None
        for e in part:
            if e["sec"] != last:
                idx += ["", "== " + e["sec"]]; last = e["sec"]
            idx.append(f"- {e['title']}  [{e['src']}]")
        for pg in sep_page(idx, "UPPSC PYQ - Index").pages: w.add_page(pg)
        for e in part:
            for pg in sep_page([e["sec"], "", "Source: " + e["src"], e["url"]], e["title"]).pages: w.add_page(pg)
            for pg in PdfReader(io.BytesIO(e["data"])).pages: w.add_page(pg)
        fn = os.path.join(OUT, f"UPPSC_PYQ_2015-2026_part{pi:02d}.pdf")
        try:
            w.compress_identical_objects(remove_duplicates=True, remove_unreferenced=True)
        except Exception as e:
            print("compress skipped", e)
        with open(fn, "wb") as f: w.write(f)
        files.append((fn, part)); print("wrote", fn, os.path.getsize(fn) // 1024, "KB")

    with open(os.path.join(OUT, "sources.txt"), "w") as f:
        for e in entries: f.write(f"{e['sec']} | {e['title']} | {e['src']} | {e['url']}\n")
        f.write("\nNOT DOWNLOADED:\n" + "\n".join(fails) + "\n")

    tok, chat = os.environ.get("TELEGRAM_BOT_TOKEN"), os.environ.get("CHAT_ID")
    if not (tok and chat):
        print("no chat; done"); return
    api = f"https://api.telegram.org/bot{tok}"
    for fn, part in files:
        secs = list(dict.fromkeys(e["sec"].split(" - ")[0] for e in part))
        cap = f"UPPSC PYQ 2015-2026 | {os.path.basename(fn)[-10:-4]} | {len(part)} papers\n" + "\n".join("• " + s[:60] for s in secs)
        for i in range(5):
            try:
                r = requests.post(f"{api}/sendDocument", data={"chat_id": chat, "caption": cap[:1000]},
                                  files={"document": open(fn, "rb")}, timeout=600)
                print(r.text[:150])
                if r.json().get("ok"): break
            except Exception as e:
                print("send err", e)
            time.sleep(15)
    requests.post(f"{api}/sendDocument", data={"chat_id": chat, "caption": f"Sources list: {len(entries)} papers, {len(fails)} not downloaded"},
                  files={"document": open(os.path.join(OUT, "sources.txt"), "rb")}, timeout=120)


if __name__ == "__main__":
    main()
