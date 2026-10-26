"""Loop the user's short face video (latest video sent to bot, muted, ping-pong) in a circle over /tmp/voice0.mp4,
mix /tmp/music.wav louder, commit + send via URL."""
import json, os, subprocess, sys, urllib.request, urllib.parse, traceback, time, shutil, re
import cv2, imageio_ffmpeg
FF = imageio_ffmpeg.get_ffmpeg_exe()
chat = sys.argv[1]; MV = sys.argv[2] if len(sys.argv) > 2 else "0.22"
tok = os.environ["TELEGRAM_BOT_TOKEN"]; API = f"https://api.telegram.org/bot{tok}"
def say(t): urllib.request.urlopen(f"{API}/sendMessage", data=urllib.parse.urlencode({"chat_id": chat, "text": t[:4000]}).encode())
try:
    get = lambda u: json.loads(urllib.request.urlopen(u, timeout=60).read())
    up = get(f"{API}/getUpdates?limit=100")["result"]; fid = None
    for u in reversed(up):
        m = u.get("message") or {}
        for k in ("video", "video_note", "animation"):
            if k in m: fid = m[k]["file_id"]; break
        if not fid and "document" in m and (m["document"].get("mime_type") or "").startswith("video"): fid = m["document"]["file_id"]
        if fid: break
    assert fid, "no video found"
    path = get(f"{API}/getFile?file_id={fid}")["result"]["file_path"]
    urllib.request.urlretrieve(f"https://api.telegram.org/file/bot{tok}/{path}", "/tmp/face.mp4")
    cap = cv2.VideoCapture("/tmp/face.mp4"); frames = []
    for _ in range(15):
        ok, fr = cap.read()
        if ok: frames.append(fr)
    assert frames, "cannot read video"
    fr = frames[-1]; H, W = fr.shape[:2]
    cc = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    fs = cc.detectMultiScale(cv2.cvtColor(fr, cv2.COLOR_BGR2GRAY), 1.1, 5, minSize=(60, 60))
    if len(fs):
        x, y, w, h = max(fs, key=lambda f: f[2]*f[3]); side = int(max(w, h) * 1.9); cx, cy = x + w//2, y + int(h*0.52)
    else:
        side = min(W, H); cx, cy = W//2, H//2
    side = min(side, W, H); x0 = max(0, min(W-side, cx-side//2)); y0 = max(0, min(H-side, cy-side//2))
    D = 250
    subprocess.run([FF, "-y", "-loglevel", "error", "-i", "/tmp/face.mp4", "-an", "-vf",
        f"crop={side}:{side}:{x0}:{y0},scale={D}:{D},fps=25,split[a][b];[b]reverse[r];[a][r]concat=n=2:v=1",
        "-c:v", "libx264", "-preset", "veryfast", "-pix_fmt", "yuv420p", "/tmp/pp.mp4"], check=True)
    R = D + 12
    fc = (f"[1:v]format=rgba,geq=r='r(X,Y)':g='g(X,Y)':b='b(X,Y)':a='if(lte(hypot(X-{D/2},Y-{D/2}),{D/2-3}),255,0)'[av];"
          f"color=c=0xec8516:s={R}x{R},format=rgba,geq=r=236:g=133:b=22:a='if(lte(hypot(X-{R/2},Y-{R/2}),{R/2}),255,0)'[ring];"
          f"[0:v][ring]overlay=W-w-22:H-h-56:shortest=1[b1];[b1][av]overlay=W-w-28:H-h-62:shortest=1[v];"
          f"[2:a]aecho=0.8:0.7:60:0.3,volume={MV}[m];[0:a][m]amix=inputs=2:duration=first:normalize=0[a]")
    subprocess.run([FF, "-y", "-loglevel", "error", "-i", "/tmp/voice0.mp4", "-stream_loop", "-1", "-i", "/tmp/pp.mp4", "-i", "/tmp/music.wav",
        "-filter_complex", fc, "-map", "[v]", "-map", "[a]", "-shortest", "-c:v", "libx264", "-preset", "veryfast", "-crf", "23", "-pix_fmt", "yuv420p",
        "-c:a", "aac", "-b:a", "160k", "-movflags", "+faststart", "/tmp/final.mp4"], check=True)
    os.makedirs("video/out", exist_ok=True); shutil.copy("/tmp/final.mp4", "video/out/avatar-loop.mp4")
    subprocess.run("git config user.name bot && git config user.email bot@users.noreply.github.com && git add video/out && "
                   "git commit -qm 'avatar loop video' && git pull -q --rebase && git push -q", shell=True, check=True)
    time.sleep(8); sha = subprocess.check_output(["git", "rev-parse", "HEAD"]).decode().strip()
    url = f"https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/{sha}/video/out/avatar-loop.mp4"
    for att in range(3):
        r = subprocess.run(["curl", "-s", "-F", f"chat_id={chat}", "-F", f"video={url}", "-F", "supports_streaming=true",
            "-F", "caption=DI — समझकर हल करें (आपका वीडियो + आवाज़)", f"{API}/sendVideo"], capture_output=True, text=True)
        print(r.stdout[:300])
        if '"ok":true' in r.stdout: break
        time.sleep(10)
    else: say("SEND FAIL " + r.stdout[:500])
except Exception:
    say("LOOP ERR\n" + traceback.format_exc()[-3000:]); raise
