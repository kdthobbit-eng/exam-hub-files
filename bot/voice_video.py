"""Fit a user's voice recording (forwarded to the bot) onto video/<dir>/segments.json frames.
Splits recording on silences into len(segments) parts, then builds video and sends it."""
import json, os, re, subprocess, sys, urllib.request
import imageio_ffmpeg
FF = imageio_ffmpeg.get_ffmpeg_exe()
d, chat = sys.argv[1], sys.argv[2]
tok = os.environ["TELEGRAM_BOT_TOKEN"]; API = f"https://api.telegram.org/bot{tok}"
def get(u): return json.loads(urllib.request.urlopen(u, timeout=60).read())
up = get(f"{API}/getUpdates?limit=100")["result"]
fid = None
for u in reversed(up):
    m = u.get("message") or {}
    for k in ("audio", "voice", "document"):
        if k in m: fid = m[k]["file_id"]; break
    if fid: break
assert fid, "no audio in updates"
path = get(f"{API}/getFile?file_id={fid}")["result"]["file_path"]
urllib.request.urlretrieve(f"https://api.telegram.org/file/bot{tok}/{path}", "/tmp/in.audio")
subprocess.run([FF, "-y", "-loglevel", "error", "-i", "/tmp/in.audio", "-ac", "1", "-ar", "44100", "/tmp/in.wav"], check=True)
segs = json.load(open(f"{d}/segments.json", encoding="utf-8")); N = len(segs)
def total():
    o = subprocess.run([FF, "-i", "/tmp/in.wav"], capture_output=True, text=True).stderr
    h, m, s = re.search(r"Duration: (\d+):(\d+):([\d.]+)", o).groups(); return int(h)*3600+int(m)*60+float(s)
T = total()
def speech(noise, dur):
    o = subprocess.run([FF, "-i", "/tmp/in.wav", "-af", f"silencedetect=noise={noise}dB:d={dur}", "-f", "null", "-"], capture_output=True, text=True).stderr
    st = [float(x) for x in re.findall(r"silence_start: ([\d.]+)", o)]; en = [float(x) for x in re.findall(r"silence_end: ([\d.]+)", o)]
    sil = list(zip(st, en + [T] * (len(st) - len(en))))
    parts, cur = [], 0.0
    for a, b in sil:
        if a - cur > 0.3: parts.append((cur, a))
        cur = b
    if T - cur > 0.3: parts.append((cur, T))
    return parts, sil
best = None
for noise in (-35, -30, -40, -45, -25):
    for dur in (2.0, 1.6, 1.3, 1.0, 0.8, 2.5):
        p, sil = speech(noise, dur)
        print(noise, dur, len(p))
        if len(p) == N: best = p; break
        if len(p) > N and (best is None or len(best) != N):
            # merge parts separated by the shortest gaps until N remain
            q = p[:]
            while len(q) > N:
                gaps = [q[i+1][0] - q[i][1] for i in range(len(q)-1)]; i = gaps.index(min(gaps))
                q[i:i+2] = [(q[i][0], q[i+1][1])]
            best = q
    if best and len(best) == N: break
assert best and len(best) == N, f"could not split into {N}"
print("parts", [(round(a,1), round(b,1)) for a, b in best])
clips = []
for i, (s, (a, b)) in enumerate(zip(segs, best)):
    a0 = max(0, a - 0.15); b0 = min(T, b + 0.2); t = (b0 - a0) + 0.4 + float(s.get("pause", 0))
    c = f"/tmp/v{i}.mp4"
    subprocess.run([FF, "-y", "-loglevel", "error", "-loop", "1", "-i", f"{d}/{s['img']}", "-ss", f"{a0:.2f}", "-to", f"{b0:.2f}", "-i", "/tmp/in.wav",
        "-af", "apad,loudnorm=I=-16:TP=-1.5", "-t", f"{t:.2f}", "-c:v", "libx264", "-preset", "veryfast", "-tune", "stillimage", "-pix_fmt", "yuv420p", "-r", "25",
        "-c:a", "aac", "-b:a", "128k", "-ar", "44100", "-ac", "2", c], check=True)
    clips.append(c)
open("/tmp/l.txt", "w").write("".join(f"file '{c}'\n" for c in clips))
out = "/tmp/voice.mp4"
subprocess.run([FF, "-y", "-loglevel", "error", "-f", "concat", "-safe", "0", "-i", "/tmp/l.txt", "-c", "copy", "-movflags", "+faststart", out], check=True)
r = subprocess.run(["curl", "-s", "-F", f"chat_id={chat}", "-F", f"video=@{out}", "-F", "supports_streaming=true",
    "-F", "caption=DI — समझकर हल करें (आपकी आवाज़ में)", f"{API}/sendVideo"], capture_output=True, text=True); print(r.stdout[:300])
