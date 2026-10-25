"""
Telegram -> Exam Hub.

Runs every few minutes on GitHub Actions (in the public exam-hub-files repo). Reads new
messages sent to the bot by an admin, saves any file into files/ (served free through
jsDelivr), writes the item to Firestore and replies in Telegram.

How to send (caption or message text):

    #UPSSSC #PDF
    UP GK One-liner Notes
    विषय: UP GK
    अध्याय: उत्तर प्रदेश एक नज़र        (optional)

Type tags: #PDF  #PYQ  #PHOTO  #QUIZ  #TEST  #NOTES  #UPDATE
Exam tag : #UPSSSC  #UPPSC  #UPPolice  #SuperTET  … (any exam name, spaces removed)
Test     : add  समय: 30   and   नेगेटिव: 0.25
Quiz/Test: put the questions below the heading lines, one blank line between questions.
Update   : #UPDATE, line 1 = heading, line 2 = one line, optional  लिंक: https://…
           and a photo for the banner.
"""
import json
import os
import re
import subprocess
import sys
import time
import urllib.parse
import urllib.request

import examhub

TOKEN = os.environ["TELEGRAM_BOT_TOKEN"]
ADMINS = {x.strip() for x in os.environ.get("ADMIN_TELEGRAM_IDS", "").split(",") if x.strip()}
REPO = os.environ.get("GITHUB_REPOSITORY", "kdthobbit-eng/exam-hub-files")
API = f"https://api.telegram.org/bot{TOKEN}"
FILE_API = f"https://api.telegram.org/file/bot{TOKEN}"

KIND_TAGS = {"pdf": "pdf", "pyq": "pyq", "photo": "image", "image": "image", "फोटो": "image",
             "quiz": "quiz", "क्विज़": "quiz", "test": "test", "mock": "test", "notes": "notes",
             "नोट्स": "notes", "ca": "notes", "update": "update", "अपडेट": "update"}


def call(method, **params):
    data = urllib.parse.urlencode({k: (json.dumps(v) if isinstance(v, (dict, list)) else v)
                                   for k, v in params.items()}).encode()
    with urllib.request.urlopen(f"{API}/{method}", data=data, timeout=60) as r:
        return json.loads(r.read())


def reply(chat_id, text):
    try:
        call("sendMessage", chat_id=chat_id, text=text[:4000])
    except Exception as e:  # never let a reply failure stop publishing
        print("reply failed:", e)


def download(file_id, name_hint):
    info = call("getFile", file_id=file_id)["result"]
    path = info["file_path"]
    ext = os.path.splitext(path)[1] or os.path.splitext(name_hint)[1] or ".bin"
    safe = re.sub(r"[^\wऀ-ॿ.-]+", "_", os.path.splitext(name_hint)[0])[:50] or "file"
    rel = f"files/{time.strftime('%Y/%m')}/{safe}_{int(time.time())}{ext}"
    os.makedirs(os.path.dirname(rel), exist_ok=True)
    urllib.request.urlretrieve(f"{FILE_API}/{path}", rel)
    return rel


def publish_files(rels):
    """Commit new files and return their permanent jsDelivr links (pinned to the commit)."""
    if not rels:
        return {}
    subprocess.run(["git", "add", *rels], check=True)
    subprocess.run(["git", "-c", "user.name=Exam Hub Bot", "-c", "user.email=bot@users.noreply.github.com",
                    "commit", "-q", "-m", f"Add {len(rels)} file(s) from Telegram"], check=True)
    subprocess.run(["git", "push", "-q"], check=True)
    sha = subprocess.check_output(["git", "rev-parse", "HEAD"]).decode().strip()
    return {r: f"https://cdn.jsdelivr.net/gh/{REPO}@{sha}/{urllib.parse.quote(r)}" for r in rels}


def parse_caption(text):
    tags = [t.lower() for t in re.findall(r"#([\wऀ-ॿ]+)", text or "")]
    kind = next((KIND_TAGS[t] for t in tags if t in KIND_TAGS), None)
    exam = next((t for t in tags if t not in KIND_TAGS), "")
    body = re.sub(r"#[\wऀ-ॿ]+", "", text or "").strip()
    meta, rest = {}, []
    for line in body.splitlines():
        m = re.match(r"^\s*(विषय|subject|अध्याय|chapter|समय|time|नेगेटिव|negative|लिंक|link|बटन|button)\s*[:：]\s*(.+)$",
                     line, re.I)
        if m:
            key = {"विषय": "subject", "अध्याय": "chapter", "समय": "minutes", "time": "minutes",
                   "नेगेटिव": "negative", "लिंक": "link", "बटन": "buttonText", "button": "buttonText"
                   }.get(m.group(1).lower(), m.group(1).lower())
            meta[key] = m.group(2).strip()
        else:
            rest.append(line)
    lines = [l for l in rest]
    while lines and not lines[0].strip():
        lines.pop(0)
    title = lines[0].strip() if lines else ""
    remaining = "\n".join(lines[1:]).strip()
    return kind, exam, title, remaining, meta


def handle(group, state):
    """group = list of messages (one message, or one Telegram album)."""
    first = group[0]
    chat_id = first["chat"]["id"]
    sender = str((first.get("from") or {}).get("id", first["chat"]["id"]))
    text = next((m.get("caption") or m.get("text") or "" for m in group if m.get("caption") or m.get("text")), "")
    admins = set(ADMINS) | set(state.get("admins", []))
    if not admins:
        # First person to talk to the bot becomes its owner (saved in Firestore settings/bot).
        state["admins"] = [sender]
        admins = {sender}
        reply(chat_id, f"✅ Aap is bot ke admin ban gaye (ID {sender}). Ab sirf aap content daal sakte hain.\n"
                       "Doosre admin jodne ke liye: /addadmin <ID>")
    if sender not in admins and str(chat_id) not in admins:
        reply(chat_id, f"Aap admin nahi hain. Apna ID admin ko bhejein: {sender}")
        return
    if text.strip().startswith("/addadmin"):
        new = text.split()[1:] if len(text.split()) > 1 else []
        if not new or not new[0].lstrip("-").isdigit():
            reply(chat_id, "Aise likhein: /addadmin 123456789")
            return
        state["admins"] = sorted(set(state.get("admins", [])) | {new[0]})
        reply(chat_id, f"✅ {new[0]} ab admin hai")
        return
    if text.strip() in ("/start", "/help", "/id"):
        reply(chat_id, f"Aapka Telegram ID: {sender}\n\n" + __doc__.split("How to send")[1])
        return
    kind, exam, title, rest, meta = parse_caption(text)

    files, photos = [], []
    for m in group:
        if m.get("document"):
            d = m["document"]
            files.append(download(d["file_id"], d.get("file_name", "file.pdf")))
        elif m.get("photo"):
            p = max(m["photo"], key=lambda x: x.get("file_size", 0))
            photos.append(download(p["file_id"], (title or "photo") + ".jpg"))
    links = publish_files(files + photos)

    if not kind:
        kind = "image" if photos and not files else ("pdf" if files else ("quiz" if examhub.parse_questions(rest) else "notes"))
    if not title:
        reply(chat_id, "Heading (title) nahi mila. Tags ke neeche pehli line me title likhein.")
        return

    if kind == "update":
        msg = examhub.add_update({
            "title": title, "subtitle": rest.splitlines()[0] if rest else "", "body": rest,
            "imageUrl": links.get(photos[0], "") if photos else "",
            "link": meta.get("link", links.get(files[0], "") if files else ""),
            "buttonText": meta.get("buttonText", ""), "tag": exam.upper() if exam else "Nayi update",
        })
    else:
        if not exam:
            reply(chat_id, "Exam ka tag nahi mila. Jaise #UPSSSC ya #UPPSC likhein.")
            return
        item = {"kind": kind, "exam": exam, "title": title, "subject": meta.get("subject"),
                "chapter": meta.get("chapter"), "text": rest, "minutes": meta.get("minutes"),
                "negative": meta.get("negative"), "source": "telegram"}
        if kind in ("pdf", "pyq"):
            item["url"] = links.get(files[0], "") if files else meta.get("link", "")
        if kind == "image":
            item["images"] = [links[p] for p in photos]
        msg = examhub.add_content(item)
    reply(chat_id, msg)


def main():
    state_ref = examhub.db().collection("settings").document("bot")
    state = (state_ref.get().to_dict() or {})
    offset = int(state.get("offset", 0))
    updates = call("getUpdates", offset=offset, timeout=0, allowed_updates=["message", "channel_post"])["result"]
    if not updates:
        print("no new messages")
        return
    groups, order = {}, []
    for u in updates:
        m = u.get("message") or u.get("channel_post")
        if not m:
            continue
        key = m.get("media_group_id") or f"m{m['message_id']}_{m['chat']['id']}"
        if key not in groups:
            groups[key] = []
            order.append(key)
        groups[key].append(m)
    for key in order:
        try:
            handle(groups[key], state)
        except Exception as e:
            print("failed:", e, file=sys.stderr)
            reply(groups[key][0]["chat"]["id"], f"❌ Nahi jud paya: {e}")
    state_ref.set({"offset": updates[-1]["update_id"] + 1, "at": examhub.now_ms(),
                   "admins": state.get("admins", [])}, merge=True)


if __name__ == "__main__":
    main()
