"""
Exam Hub branding for any PDF or set of photos.

Every page gets a top band "Exam Hub 2026" (with logo) and a bottom band with clickable
Telegram, App and Website links. The original page is shrunk into the space between the
bands, so nothing on the paper is covered.

  python3 brand_pdf.py out.pdf  paper.pdf
  python3 brand_pdf.py out.pdf  p1.jpg p2.jpg p3.jpg       (photos -> one PDF, in order)
"""
import os
import sys

import pymupdf

HERE = os.path.dirname(os.path.abspath(__file__))
FONT = os.path.join(HERE, "fonts", "baloo2_semibold.ttf")
FONT_B = os.path.join(HERE, "fonts", "baloo2_bold.ttf")
LOGO = os.path.join(HERE, "logo.png")

TITLE = "Exam Hub 2026"
# (label, url, share of the footer width)
LINKS = [
    ("Telegram से जुड़ें → @examhub2026", "https://t.me/examhub2026", 0.38),
    ("पूरा पेपर Telegram में Attempt करें → @uppetexamhub", "https://t.me/uppetexamhub", 0.62),
]
TAG = ("पूरा पेपर Telegram में Attempt करें → t.me/uppetexamhub", "https://t.me/uppetexamhub")
GREEN = (1 / 255, 90 / 255, 62 / 255)
SAFFRON = (236 / 255, 133 / 255, 22 / 255)
A4 = pymupdf.paper_rect("a4")
TOP, BOTTOM = 34, 30


def _source_pages(inputs):
    """Yield (doc, page_number) for every input page; photos become one page each."""
    for path in inputs:
        ext = os.path.splitext(path)[1].lower()
        if ext == ".pdf":
            doc = pymupdf.open(path)
            for i in range(doc.page_count):
                yield doc, i
        else:
            img = pymupdf.open(path)
            pdf = pymupdf.open("pdf", img.convert_to_pdf())
            yield pdf, 0


_ARCH = pymupdf.Archive(os.path.join(HERE, "fonts"))
_CSS = "@font-face{font-family:B;src:url(baloo2_semibold.ttf)} *{font-family:B;margin:0}"


def _html(page, rect, text, size, color, align):
    page.insert_htmlbox(rect, f'<div style="font-size:{size}px;color:{color};text-align:{align};line-height:1">{text}</div>',
                        css=_CSS, archive=_ARCH)


def brand(out_path, inputs, title=TITLE, band=None, logo=LOGO, links=None, text_hex="#015a3e", tag_hex="#ffdb99"):
    band = band or GREEN
    links = links or LINKS
    out = pymupdf.open()
    for src, pno in _source_pages(inputs):
        sp = src[pno]
        landscape = sp.rect.width > sp.rect.height
        page = out.new_page(width=A4.height if landscape else A4.width,
                            height=A4.width if landscape else A4.height)
        W, H = page.rect.width, page.rect.height
        tgt = pymupdf.Rect(0, TOP, W, H - BOTTOM)
        page.show_pdf_page(tgt, src, pno, keep_proportion=True)
        # keep clickable links of the source page (show_pdf_page drops them)
        sr = sp.rect
        k = min(tgt.width / sr.width, tgt.height / sr.height)
        ox = tgt.x0 + (tgt.width - sr.width * k) / 2
        oy = tgt.y0 + (tgt.height - sr.height * k) / 2
        for ln in sp.get_links():
            if ln.get("uri"):
                r = ln["from"]
                page.insert_link({"kind": pymupdf.LINK_URI, "uri": ln["uri"],
                                  "from": pymupdf.Rect(ox + r.x0 * k, oy + r.y0 * k, ox + r.x1 * k, oy + r.y1 * k)})

        # top band
        page.draw_rect(pymupdf.Rect(0, 0, W, TOP), color=None, fill=band)
        if logo and os.path.exists(logo):
            page.insert_image(pymupdf.Rect(8, 3, 8 + TOP - 6, TOP - 3), filename=logo)
        page.insert_font(fontname="bb", fontfile=FONT_B)
        page.insert_font(fontname="bs", fontfile=FONT)
        page.insert_text((TOP + 10 if logo else 14, TOP - 10), title, fontname="bb", fontsize=15, color=(1, 1, 1))
        tr = pymupdf.Rect(W * 0.42, 9, W - 10, TOP - 4)
        _html(page, tr, TAG[0], 10, tag_hex, "right")
        page.insert_link({"kind": pymupdf.LINK_URI, "uri": TAG[1], "from": tr})

        # bottom band with three links
        page.draw_rect(pymupdf.Rect(0, H - BOTTOM, W, H), color=None, fill=(0.96, 0.97, 0.96))
        page.draw_line((0, H - BOTTOM), (W, H - BOTTOM), color=SAFFRON, width=1.2)
        x0 = 0
        for label, url, share in links:
            col = (W - 26) * share
            r = pymupdf.Rect(x0 + 6, H - BOTTOM + 7, x0 + col - 6, H - 2)
            _html(page, r, label, 10, text_hex, "center")
            page.insert_link({"kind": pymupdf.LINK_URI, "uri": url, "from": r})
            x0 += col
        num = f"{page.number + 1}"
        page.insert_text((W - 18, H - BOTTOM / 2 + 3), num, fontname="helv", fontsize=7.5, color=(0.45, 0.5, 0.48))
    out.set_metadata({"title": title, "author": "Exam Hub", "subject": "erojgarresult.com/exam-hub"})
    out.save(out_path, garbage=3, deflate=True)
    return out_path


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(__doc__)
        sys.exit(1)
    print(brand(sys.argv[1], sys.argv[2:]))
