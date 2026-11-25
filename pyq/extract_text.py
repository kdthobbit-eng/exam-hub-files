"""Download PCS Prelims GS-I papers and save their text (pdftotext, OCR fallback) to pyq/text/."""
import os, subprocess, requests
UA = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124 Safari/537.36"}
P = "https://cdn-images.prepp.in/public/image/"
PAPERS = {
 "2018": P + "9ab1ca9fa32d54efc2455e6e129c14f4.pdf",
 "2019": P + "ac5ee3f6138da53d94bcb0890d4d8f02.pdf",
 "2020": P + "742fb42b728b94873cd3cdb623ab7cd1.pdf",
 "2021": "https://cdn-images.prepp.in/public/image/8b953dba7313d",  # prefix only; replaced below if scraped
 "2022": P + "417623a799425c4b9ffc29f5f1ecf06f.pdf",
 "2023": P + "UPPCS_2023__9891ef50b1f45dafae1cc0f9d8e0ee13.pdf",
 "2024": P + "uppsc_GS_Shift_1_compressed_e7fceaed9faf8e9696bc2ee92972fbf1.pdf",
 "2025": "https://uppsc.up.nic.in/Open_PDF.aspx?I4PnQ0tBagkkSr8SRVPayUNByQmkPMq/",
}
KEYS = {
 "2018": P + "uppsc-prelims-gs-answer-key-pdf-oct-28-2018-1759926300.pdf",
 "2019": P + "uppsc-prelims-gs-answer-key-pdf-dec-15-2019-1759926300.pdf",
 "2020": P + "uppsc-prelims-gs-answer-key-pdf-oct-11-2020-1759926300.pdf",
 "2022": P + "uppsc-prelims-gs-answer-key-pdf-jun-12-2022-1759926300.pdf",
 "2023": P + "uppsc-prelims-gs-answer-key-pdf-may-14-2023-1759926300.pdf",
 "2024": P + "UP_PCS_2024_Prelims_GS_1_Paper_with_Answer_Key_compressed_ceb098bdd62ff8598606f70a63d220cd.pdf",
}
out = "pyq/text"; os.makedirs(out, exist_ok=True)
s = requests.Session(); s.headers.update(UA)
s.get("https://uppsc.up.nic.in/OuterPages/PreQuesPapers.aspx?ID=PrevQues", timeout=60)
def fetch(name, url):
    try:
        r = s.get(url, timeout=120, headers={"Referer": "https://uppsc.up.nic.in/OuterPages/PreQuesPapers.aspx?ID=PrevQues"})
    except Exception as e:
        print("ERR", name, e); return
    if not r.content.startswith(b"%PDF"):
        print("NOT PDF", name, r.status_code); return
    pdf = f"/tmp/{name}.pdf"; open(pdf, "wb").write(r.content)
    txt = f"{out}/{name}.txt"
    subprocess.run(["pdftotext", "-layout", pdf, txt])
    t = open(txt, errors="ignore").read()
    if len(t.strip()) < 3000 or sum(c.isalpha() and c.isascii() for c in t) < 2000:
        print("OCR", name)
        subprocess.run(["pdftoppm", "-r", "200", "-png", pdf, f"/tmp/{name}_p"])
        pages = sorted(f for f in os.listdir("/tmp") if f.startswith(f"{name}_p"))
        with open(txt, "w") as f:
            for pg in pages:
                res = subprocess.run(["tesseract", f"/tmp/{pg}", "-", "-l", "eng+hin"], capture_output=True, text=True)
                f.write(f"\n=== {pg} ===\n" + res.stdout)
    print("OK", name, os.path.getsize(txt))
for y, u in PAPERS.items():
    if u.endswith(".pdf") or "Open_PDF" in u:
        fetch(f"gs1_{y}", u)
for y, u in KEYS.items():
    fetch(f"key_{y}", u)
