"""Download the latest document sent to the bot into inbox_files/ and commit."""
import json, os, subprocess, urllib.request
tok = os.environ["TELEGRAM_BOT_TOKEN"]; API = f"https://api.telegram.org/bot{tok}"
get = lambda u: json.loads(urllib.request.urlopen(u, timeout=60).read())
up = get(f"{API}/getUpdates?limit=100")["result"]
for u in reversed(up):
    m = u.get("message") or {}
    if "document" in m:
        d = m["document"]; break
else: raise SystemExit("no document")
p = get(f"{API}/getFile?file_id={d['file_id']}")["result"]["file_path"]
os.makedirs("inbox_files", exist_ok=True); out = "inbox_files/" + d.get("file_name", "file.bin")
urllib.request.urlretrieve(f"https://api.telegram.org/file/bot{tok}/{p}", out)
subprocess.run("git config user.name bot && git config user.email bot@users.noreply.github.com && git add inbox_files && git commit -qm 'fetch doc' && git pull -q --rebase && git push -q", shell=True, check=True)
print(out)
