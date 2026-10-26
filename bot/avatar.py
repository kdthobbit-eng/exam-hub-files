"""Simple talking-photo avatar (no external models): animates the mouth of the user's photo by voice loudness
(OpenCV warp), overlays it in a circle on /tmp/voice0.mp4, mixes /tmp/music.wav, sends via bot."""
import json, os, subprocess, sys, urllib.request, urllib.parse, traceback, wave, math
import numpy as np, cv2, imageio_ffmpeg
FF = imageio_ffmpeg.get_ffmpeg_exe()
chat = sys.argv[1]; tok = os.environ["TELEGRAM_BOT_TOKEN"]; API = f"https://api.telegram.org/bot{tok}"
def say(t): urllib.request.urlopen(f"{API}/sendMessage", data=urllib.parse.urlencode({"chat_id": chat, "text": t[:4000]}).encode())
try:
    get = lambda u: json.loads(urllib.request.urlopen(u, timeout=60).read())
    up = get(f"{API}/getUpdates?limit=100")["result"]; fid = None
    for u in reversed(up):
        m = u.get("message") or {}
        if "photo" in m: fid = m["photo"][-1]["file_id"]; break
    assert fid, "no photo"
    path = get(f"{API}/getFile?file_id={fid}")["result"]["file_path"]
    urllib.request.urlretrieve(f"https://api.telegram.org/file/bot{tok}/{path}", "/tmp/face_full.jpg")
    img = cv2.imread("/tmp/face_full.jpg"); H, W = img.shape[:2]
    cc = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    fs = cc.detectMultiScale(cv2.cvtColor(img, cv2.COLOR_BGR2GRAY), 1.1, 5, minSize=(60, 60))
    assert len(fs), "face not found in photo"
    x, y, w, h = max(fs, key=lambda f: f[2]*f[3])
    side = int(max(w, h) * 1.9); cx, cy = x + w//2, y + int(h*0.52)
    x0 = max(0, min(W-side, cx - side//2)); y0 = max(0, min(H-side, cy - side//2)); side = min(side, W, H)
    S = 360; k = S / side
    crop = cv2.resize(img[y0:y0+side, x0:x0+side], (S, S))
    fx, fy, fw, fh = (x-x0)*k, (y-y0)*k, w*k, h*k
    mx, my, mw = fx + fw/2, fy + fh*0.80, fw*0.36          # mouth centre / width
    chin = fy + fh*1.08; A = fh*0.075                         # max jaw drop (px)
    # loudness envelope at 25 fps
    subprocess.run([FF, "-y", "-loglevel", "error", "-i", "/tmp/voice0.mp4", "-vn", "-ac", "1", "-ar", "16000", "/tmp/v16.wav"], check=True)
    wv = wave.open("/tmp/v16.wav"); a = np.frombuffer(wv.readframes(wv.getnframes()), np.int16).astype(np.float32); sr = 16000
    fps = 25; hop = sr // fps; n = len(a) // hop
    rms = np.sqrt((a[:n*hop].reshape(n, hop)**2).mean(1)); db = 20*np.log10(rms+1)
    lo, hi = np.percentile(db, 30), np.percentile(db, 97)
    o = np.clip((db - lo) / (hi - lo + 1e-6), 0, 1)
    sm = np.zeros_like(o)
    for i in range(n): sm[i] = (0.55*sm[i-1] + 0.45*o[i]) if i else o[i]
    # jitter so mouth flickers like syllables
    sm = sm * (0.75 + 0.25*np.abs(np.sin(np.arange(n)*1.7)))
    yy, xx = np.mgrid[0:S, 0:S].astype(np.float32)
    gx = np.exp(-((xx-mx)/(mw*0.75))**2)
    def ramp(v): return np.clip(v, 0, 1)
    sy = ramp((yy-(my-2))/6) * ramp((chin + fh*0.35 - yy)/(fh*0.35))
    base = gx * sy
    p = subprocess.Popen([FF, "-y", "-loglevel", "error", "-f", "rawvideo", "-pix_fmt", "bgr24", "-s", f"{S}x{S}", "-r", str(fps), "-i", "-",
                          "-c:v", "libx264", "-preset", "veryfast", "-pix_fmt", "yuv420p", "/tmp/avatar.mp4"], stdin=subprocess.PIPE)
    for i in range(n):
        d = A * sm[i]
        f = cv2.remap(crop, xx, (yy - d*base).astype(np.float32), cv2.INTER_LINEAR, borderMode=cv2.BORDER_REPLICATE)
        if d > 1.2:
            ov = f.copy()
            cv2.ellipse(ov, (int(mx), int(my + d*0.45)), (int(mw*0.42), max(1, int(d*0.55))), 0, 0, 360, (35, 25, 45), -1)
            ov = cv2.GaussianBlur(ov, (5, 5), 0)
            f = cv2.addWeighted(ov, 0.8, f, 0.2, 0)
        # gentle head sway
        ang = 1.2*math.sin(i/fps*0.9); ty = 2*math.sin(i/fps*1.3)
        M = cv2.getRotationMatrix2D((S/2, S*0.8), ang, 1.0); M[1, 2] += ty
        f = cv2.warpAffine(f, M, (S, S), borderMode=cv2.BORDER_REPLICATE)
        p.stdin.write(f.tobytes())
    p.stdin.close(); p.wait()
    D = 250; R = D + 12
    fc = (f"[1:v]scale={D}:{D},format=rgba,geq=r='r(X,Y)':g='g(X,Y)':b='b(X,Y)':a='if(lte(hypot(X-{D/2},Y-{D/2}),{D/2-3}),255,0)'[av];"
          f"color=c=0xec8516:s={R}x{R},format=rgba,geq=r=236:g=133:b=22:a='if(lte(hypot(X-{R/2},Y-{R/2}),{R/2}),255,0)'[ring];"
          f"[0:v][ring]overlay=W-w-22:H-h-56:shortest=1[b1];[b1][av]overlay=W-w-28:H-h-62:shortest=1[v];"
          f"[2:a]aecho=0.8:0.7:60:0.3,volume=0.10[m];[0:a][m]amix=inputs=2:duration=first:normalize=0[a]")
    subprocess.run([FF, "-y", "-loglevel", "error", "-i", "/tmp/voice0.mp4", "-i", "/tmp/avatar.mp4", "-i", "/tmp/music.wav", "-filter_complex", fc,
                    "-map", "[v]", "-map", "[a]", "-c:v", "libx264", "-preset", "veryfast", "-crf", "23", "-pix_fmt", "yuv420p",
                    "-c:a", "aac", "-b:a", "160k", "-movflags", "+faststart", "/tmp/final.mp4"], check=True)
    import time
    for att in range(4):
        kind = "video" if att < 3 else "document"
        r = subprocess.run(["curl", "-s", "-F", f"chat_id={chat}", "-F", f"{kind}=@/tmp/final.mp4", "-F", "supports_streaming=true",
            "-F", "caption=DI — समझकर हल करें (आपकी आवाज़ + चेहरा)", f"{API}/send{kind.capitalize()}"], capture_output=True, text=True)
        print(r.stdout[:300])
        if '"ok":true' in r.stdout: break
        time.sleep(10)
    else:
        say("SEND FAIL size=%d %s" % (os.path.getsize("/tmp/final.mp4"), r.stdout[:500]))
except Exception:
    say("AVATAR ERR\n" + traceback.format_exc()[-3000:]); raise
