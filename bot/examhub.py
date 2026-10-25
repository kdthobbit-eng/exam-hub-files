"""
Exam Hub content publisher.

Writes PDFs, photos, quizzes, mock tests, notes and live updates into the same
Firestore the app and erojgarresult.com read. Used by:
  - telegram_bot.py  (you send a file/text to the Telegram bot)
  - inbox.py         (Claude commits a JSON file into inbox/)

Firestore layout (same as the Android app):
  classes/{examId}/subjects/{subjectId}/chapters/{chapterId}/content/{id}
  sections/{id}   updates/{id}   pdfs/{id}
"""
import json
import os
import re
import time
import unicodedata

import firebase_admin
from firebase_admin import credentials, firestore

_db = None


def db():
    global _db
    if _db is None:
        info = json.loads(os.environ["FIREBASE_SERVICE_ACCOUNT"])
        firebase_admin.initialize_app(credentials.Certificate(info))
        _db = firestore.client()
    return _db


# ------------------------------------------------------------------ helpers

def norm(s: str) -> str:
    s = unicodedata.normalize("NFKC", s or "").lower()
    return re.sub(r"[\s\-_./]+", "", s)


def slug(s: str) -> str:
    s = re.sub(r"[^\wऀ-ॿ]+", "_", (s or "").strip().lower()).strip("_")
    return (s or "item")[:40] + "_" + str(int(time.time() * 1000))[-6:]


def now_ms() -> int:
    return int(time.time() * 1000)


# ------------------------------------------------------------------ question parser
# Same rules as the app's QuestionParser.kt.

_OPT = re.compile(r"^\s*\*?\s*\(?([A-Ea-eकखगघङ])[\)\.:]\s*(.+)$")
_ANS = re.compile(r"^\s*(?:उत्तर|सही उत्तर|Ans(?:wer)?|Correct)\s*[:\-–]\s*\(?([A-Ea-e1-5कखगघङ])", re.I)
_EXP = re.compile(r"^\s*(?:व्याख्या|Explanation|Exp|Hint)\s*[:\-–]\s*(.*)$", re.I)
_NUM = re.compile(r"^\s*(?:Q\.?\s*)?\d+\s*[\.\)\:]\s*", re.I)
_IDX = {"a": 0, "1": 0, "क": 0, "b": 1, "2": 1, "ख": 1, "c": 2, "3": 2, "ग": 2, "d": 3, "4": 3, "घ": 3, "e": 4, "5": 4, "ङ": 4}


def parse_questions(text: str):
    out = []
    for block in re.split(r"\n\s*\n", (text or "").replace("\r", "")):
        lines = [l.rstrip() for l in block.splitlines() if l.strip()]
        if len(lines) < 3:
            continue
        q, opts, ans, star, exp = [], [], -1, -1, ""
        for line in lines:
            a, e, o = _ANS.match(line), _EXP.match(line), _OPT.match(line)
            if a:
                ans = _IDX.get(a.group(1).lower(), -1)
            elif e:
                exp = e.group(1).strip()
            elif o and len(opts) < 5 and q:
                if line.lstrip().startswith("*"):
                    star = len(opts)
                opts.append(o.group(2).strip())
            elif not opts:
                q.append(line)
            elif exp:
                exp += " " + line.strip()
        if ans < 0:
            ans = star
        question = _NUM.sub("", " ".join(q), count=1).strip()
        if question and len(opts) >= 2 and 0 <= ans < len(opts):
            out.append({
                "q": question, "q_hi": question, "options": opts, "options_hi": opts,
                "answer": ans, "explain": exp, "explain_hi": exp,
            })
    return out


# ------------------------------------------------------------------ lookups

def exams():
    return [(d.id, d.to_dict() or {}) for d in db().collection("classes").stream()]


def find_exam(name: str):
    """Matches 'UPSSSC', '#UPPolice', 'up police', 'सुपर टेट' style names to an exam id."""
    n = norm(name).lstrip("#")
    if not n:
        return None
    for eid, d in exams():
        for key in (d.get("name_en", ""), d.get("name_hi", ""), eid.replace("demo_", "")):
            if key and norm(key) == n:
                return eid, d
    return None


SECTION_FOR = {"test": "test", "quiz": "quiz", "pdf": "pdf", "pyq": "pdf", "image": "image", "notes": "notes"}


def find_section(kind: str):
    want = SECTION_FOR.get(kind, kind)
    secs = [(d.id, d.to_dict() or {}) for d in db().collection("sections").stream()]
    secs = [s for s in secs if s[1].get("type") == want]
    if kind == "pyq":
        for s in secs:
            if "pyq" in norm(s[1].get("title_en", "")) or "प्रश्नपत्र" in s[1].get("title_hi", ""):
                return s[0]
    if kind == "notes":
        for s in secs:
            if "current" in norm(s[1].get("title_en", "")):
                return s[0]
    return secs[0][0] if secs else ""


def ensure_subject(exam_id: str, title: str) -> str:
    col = db().collection("classes").document(exam_id).collection("subjects")
    title = (title or "सामान्य").strip()
    for d in col.stream():
        x = d.to_dict() or {}
        if norm(title) in (norm(x.get("title_en", "")), norm(x.get("title_hi", ""))):
            return d.id
    ref = col.document(slug(title))
    ref.set({"title_en": title, "title_hi": title, "emoji": "📘", "color": "#A7F3D0",
             "order": 900, "draft": False, "createdAt": now_ms()})
    return ref.id


def ensure_chapter(exam_id: str, subject_id: str, title: str) -> str:
    col = (db().collection("classes").document(exam_id).collection("subjects")
           .document(subject_id).collection("chapters"))
    title = (title or "सभी").strip()
    for d in col.stream():
        x = d.to_dict() or {}
        if norm(title) in (norm(x.get("title_en", "")), norm(x.get("title_hi", ""))):
            return d.id
    ref = col.document(slug(title))
    ref.set({"title_en": title, "title_hi": title, "order": 900, "draft": False, "createdAt": now_ms()})
    return ref.id


# ------------------------------------------------------------------ publish

def add_content(item: dict) -> str:
    """
    item keys:
      kind      test | quiz | pdf | pyq | image | notes
      exam      exam name, e.g. "UPSSSC"
      subject   subject name (created if missing), default "सामान्य"
      chapter   chapter name (created if missing), default "सभी"
      title     required
      text      notes body, or questions in the paste format (quiz/test)
      url       PDF link
      images    list of photo links
      minutes / negative   for tests
    Returns a one-line Hindi confirmation.
    """
    kind = item["kind"]
    found = find_exam(item.get("exam", ""))
    if not found:
        names = ", ".join(d.get("name_en", "") for _, d in exams())
        raise ValueError(f"Exam '{item.get('exam')}' nahi mila. Inme se likhein: {names}")
    exam_id, exam = found
    subject_id = ensure_subject(exam_id, item.get("subject"))
    chapter_id = ensure_chapter(exam_id, subject_id, item.get("chapter"))
    title = item["title"].strip()
    fields = {
        "chapterId": chapter_id, "sectionId": find_section(kind),
        "type": SECTION_FOR.get(kind, kind),
        "title_en": title, "title_hi": title, "body_en": "", "body_hi": "",
        "url": item.get("url", ""), "pdfId": "", "images": item.get("images", []),
        "minutes": int(item.get("minutes") or 0), "negative": float(item.get("negative") or 0),
        "order": 0, "draft": False, "createdAt": now_ms(), "source": item.get("source", "bot"),
    }
    extra = ""
    if kind in ("quiz", "test"):
        qs = item.get("questions") or parse_questions(item.get("text", ""))
        if not qs:
            raise ValueError("Ek bhi prashn samajh nahi aaya. Format: 1. प्रश्न / A) … D) … / उत्तर: B")
        fields["questions"] = qs
        extra = f" ({len(qs)} prashn)"
    elif kind == "notes":
        fields["body_en"] = fields["body_hi"] = item.get("text", "")
    elif kind == "image":
        if not fields["images"]:
            raise ValueError("Photo ka link nahi mila")
        fields["url"] = fields["images"][0]
        fields["body_hi"] = fields["body_en"] = item.get("text", "")
    elif kind in ("pdf", "pyq") and not fields["url"]:
        raise ValueError("PDF ka link nahi mila")
    # newest first: order = negative timestamp keeps new items on top
    fields["order"] = -int(time.time()) // 60
    ref = (db().collection("classes").document(exam_id).collection("subjects").document(subject_id)
           .collection("chapters").document(chapter_id).collection("content").document(slug(title)))
    ref.set(fields)
    label = {"test": "Mock Test", "quiz": "Quiz", "pdf": "PDF", "pyq": "PYQ", "image": "Photo", "notes": "Notes"}[kind]
    return f"✅ {exam.get('name_en', exam_id)} › {item.get('subject') or 'सामान्य'}: {label} '{title}' jud gaya{extra}"


def add_update(item: dict) -> str:
    """Live Update card on Home: title, subtitle, body, imageUrl, link, buttonText."""
    col = db().collection("updates")
    fields = {
        "tag": item.get("tag", "Nayi update"), "title": item["title"].strip(),
        "subtitle": item.get("subtitle", ""), "body": item.get("body", item.get("text", "")),
        "imageUrl": item.get("imageUrl", ""), "link": item.get("link", ""),
        "buttonText": item.get("buttonText", "Kholein" if item.get("link") else ""),
        "order": -int(time.time()) // 60, "draft": False, "updatedAt": now_ms(),
    }
    col.document(slug(fields["title"])).set(fields)
    return f"✅ Live Update '{fields['title']}' Home par lag gaya"
