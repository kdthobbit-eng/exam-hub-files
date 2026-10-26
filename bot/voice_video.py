"""Fit a user's voice recording (forwarded to the bot) onto video/<dir>/segments.json frames.
Splits recording on silences into len(segments) parts, then builds video and sends it."""
import json, os, re, subprocess, sys, urllib.request, urllib.parse
import imageio_ffmpeg
FF = imageio_ffmpeg.get_ffmpeg_exe()
import traceback
LOG=[]
_p=print
def print(*a):
    LOG.append(' '.join(map(str,a))); _p(*a)
def run():
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
    import numpy as np, wave
    w=wave.open("/tmp/in.wav"); sr=w.getframerate(); x=np.frombuffer(w.readframes(w.getnframes()),dtype=np.int16).astype(np.float32)
    hop=int(sr*0.05); n=len(x)//hop; e=np.sqrt((x[:n*hop].reshape(n,hop)**2).mean(1))+1e-6; db=20*np.log10(e)
    thr=np.percentile(db,20)+6; quiet=db<thr
    gaps=[];i=0
    while i<n:
        if quiet[i]:
            j=i
            while j<n and quiet[j]: j+=1
            if i>0 and j<n: gaps.append((j-i,i,j))
            i=j
        else: i+=1
    gaps.sort(reverse=True); cut=sorted(gaps[:N-1],key=lambda g:g[1])
    print("T",round(T,1),"thr",round(float(thr),1),"gaps",len(gaps),"chosen",[round(g[0]*0.05,2) for g in cut])
    # speech start/end
    loud=np.where(~quiet)[0]; s0=loud[0]*0.05; s1=(loud[-1]+1)*0.05
    edges=[s0]+[x for g in cut for x in (g[1]*0.05,g[2]*0.05)]+[s1]
    best=[(edges[k],edges[k+1]) for k in range(0,len(edges),2)]
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

try:
    run()
except Exception:
    tok=os.environ["TELEGRAM_BOT_TOKEN"]
    msg=("ERR\n"+"\n".join(LOG[-25:])+"\n"+traceback.format_exc()[-1500:])[:4000]
    urllib.request.urlopen(f"https://api.telegram.org/bot{tok}/sendMessage",data=urllib.parse.urlencode({"chat_id":sys.argv[2],"text":msg}).encode())
    raise
