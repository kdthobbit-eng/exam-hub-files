"""Download all recent PDF documents sent to the bot into inbox_files/ and commit."""
import json, os, subprocess, urllib.request
tok = os.environ["TELEGRAM_BOT_TOKEN"]; API = f"https://api.telegram.org/bot{tok}"
get = lambda u: json.loads(urllib.request.urlopen(u, timeout=60).read())
up = get(f"{API}/getUpdates?limit=100")["result"]
os.makedirs("inbox_files", exist_ok=True); n = 0
for u in up:
    m = u.get("message") or {}
    d = m.get("document")
    if not d or not d.get("file_name", "").lower().endswith(".pdf"): continue
    p = get(f"{API}/getFile?file_id={d['file_id']}")["result"]["file_path"]
    urllib.request.urlretrieve(f"https://api.telegram.org/file/bot{tok}/{p}", "inbox_files/" + d["file_name"]); n += 1
print(n, "files")
subprocess.run("git config user.name bot && git config user.email bot@users.noreply.github.com && git add inbox_files && git commit -qm 'fetch all docs' && git pull -q --rebase && git push -q", shell=True, check=True)
