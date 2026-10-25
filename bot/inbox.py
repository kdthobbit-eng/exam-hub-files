"""
Claude -> Exam Hub.

Claude (or anyone with repo access) drops a JSON file into inbox/ and pushes. This script
publishes every inbox file to Firestore and moves it to done/ (or failed/ with the reason).

One item, or a list of items, per file:

  {"kind": "quiz", "exam": "UPSSSC", "subject": "UP GK", "title": "आज का UP GK क्विज़",
   "text": "1. प्रश्न…\nA) …\nB) …\nC) …\nD) …\nउत्तर: B\nव्याख्या: …"}

  {"kind": "pdf", "exam": "UPPSC", "subject": "संविधान", "title": "मौलिक अधिकार नोट्स",
   "file": "files/2026/10/maulik_adhikar.pdf"}          # a file committed in this repo
  {"kind": "image", "exam": "UPSSSC", "title": "UP का नक्शा", "files": ["files/…/map.jpg"]}
  {"kind": "test", …, "minutes": 30, "negative": 0.25, "text": "…questions…"}
  {"kind": "notes", …, "text": "• one-liner 1\n• one-liner 2"}
  {"kind": "update", "title": "UPSSSC PET 2026 Solved Paper", "subtitle": "Kal 10 baje",
   "body": "…", "link": "https://…", "buttonText": "PDF dekhein", "image": "files/…/banner.jpg"}
"""
import glob
import json
import os
import shutil
import subprocess
import urllib.parse

import examhub

REPO = os.environ.get("GITHUB_REPOSITORY", "kdthobbit-eng/exam-hub-files")


def cdn(rel, sha):
    return f"https://cdn.jsdelivr.net/gh/{REPO}@{sha}/{urllib.parse.quote(rel)}"


def main():
    paths = sorted(glob.glob("inbox/*.json"))
    if not paths:
        print("inbox empty")
        return
    sha = subprocess.check_output(["git", "rev-parse", "HEAD"]).decode().strip()
    os.makedirs("done", exist_ok=True)
    os.makedirs("failed", exist_ok=True)
    report = []
    for p in paths:
        try:
            data = json.load(open(p, encoding="utf-8"))
            items = data if isinstance(data, list) else [data]
            for it in items:
                if it.get("file"):
                    it["url"] = cdn(it["file"], sha)
                if it.get("files"):
                    it["images"] = [cdn(f, sha) for f in it["files"]]
                if it.get("image"):
                    it["imageUrl"] = cdn(it["image"], sha)
                it.setdefault("source", "claude")
                msg = examhub.add_update(it) if it.get("kind") == "update" else examhub.add_content(it)
                report.append(msg)
                print(msg)
            shutil.move(p, os.path.join("done", os.path.basename(p)))
        except Exception as e:
            report.append(f"❌ {os.path.basename(p)}: {e}")
            print(report[-1])
            shutil.move(p, os.path.join("failed", os.path.basename(p)))
            with open(os.path.join("failed", os.path.basename(p) + ".error.txt"), "w", encoding="utf-8") as f:
                f.write(str(e))
    with open(os.environ.get("GITHUB_STEP_SUMMARY", os.devnull), "a", encoding="utf-8") as f:
        f.write("\n".join(f"- {r}" for r in report) + "\n")
    subprocess.run(["git", "add", "-A", "inbox", "done", "failed"], check=True)
    subprocess.run(["git", "-c", "user.name=Exam Hub Bot", "-c", "user.email=bot@users.noreply.github.com",
                    "commit", "-q", "-m", "Publish inbox"], check=False)
    subprocess.run(["git", "push", "-q"], check=False)


if __name__ == "__main__":
    main()
