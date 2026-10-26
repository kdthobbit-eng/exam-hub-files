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
    TEMPO = float(sys.argv[3]) if len(sys.argv) > 3 else 0.88
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
        a0 = max(0, a - 0.15); b0 = min(T, b + 0.2); t = (b0 - a0) / TEMPO + 0.4 + float(s.get("pause", 0))
        c = f"/tmp/v{i}.mp4"
        subprocess.run([FF, "-y", "-loglevel", "error", "-loop", "1", "-i", f"{d}/{s['img']}", "-ss", f"{a0:.2f}", "-to", f"{b0:.2f}", "-i", "/tmp/in.wav",
            "-af", f"atempo={TEMPO},apad,loudnorm=I=-16:TP=-1.5", "-t", f"{t:.2f}", "-c:v", "libx264", "-preset", "veryfast", "-tune", "stillimage", "-pix_fmt", "yuv420p", "-r", "25",
            "-c:a", "aac", "-b:a", "128k", "-ar", "44100", "-ac", "2", c], check=True)
        clips.append(c)
    open("/tmp/l.txt", "w").write("".join(f"file '{c}'\n" for c in clips))
    out0 = "/tmp/voice0.mp4"
    subprocess.run([FF, "-y", "-loglevel", "error", "-f", "concat", "-safe", "0", "-i", "/tmp/l.txt", "-c", "copy", out0], check=True)
    # soft generated background music (royalty-free): slow piano-like arpeggios C-Am-F-G
    import numpy as np, wave
    o = subprocess.run([FF, "-i", out0], capture_output=True, text=True).stderr
    h, mi, se = re.search(r"Duration: (\d+):(\d+):([\d.]+)", o).groups(); L = int(h)*3600+int(mi)*60+float(se)
    sr = 44100; mus = np.zeros(int(sr*(L+2)))
    chords = [[261.6,329.6,392.0,523.3],[220.0,261.6,329.6,440.0],[174.6,220.0,261.6,349.2],[196.0,246.9,293.7,392.0]]
    note = 0.6; t = np.arange(int(sr*2.4))/sr
    k = 0; pos = 0
    while pos < len(mus):
        ch = chords[(k//8) % 4]; f = ch[[0,1,2,3,2,1,0,2][k % 8]]
        tone = (np.sin(2*np.pi*f*t) + 0.3*np.sin(2*np.pi*2*f*t) + 0.15*np.sin(2*np.pi*3*f*t)) * np.exp(-t*2.2)
        end = min(len(mus), pos+len(tone)); mus[pos:end] += tone[:end-pos]
        if k % 8 == 0:
            pad = sum(np.sin(2*np.pi*(c/2)*np.arange(int(sr*note*8))/sr) for c in ch[:3]) * 0.12
            e2 = min(len(mus), pos+len(pad)); mus[pos:e2] += pad[:e2-pos] * np.hanning(len(pad))[:e2-pos]
        pos += int(sr*note); k += 1
    mus = mus / (np.abs(mus).max()+1e-9) * 0.8
    fade = int(sr*3); mus[:fade] *= np.linspace(0,1,fade); mus[-fade:] *= np.linspace(1,0,fade)
    w = wave.open("/tmp/music.wav","w"); w.setnchannels(1); w.setsampwidth(2); w.setframerate(sr); w.writeframes((mus*32767).astype(np.int16).tobytes()); w.close()
    out = "/tmp/voice.mp4"
    subprocess.run([FF, "-y", "-loglevel", "error", "-i", out0, "-i", "/tmp/music.wav", "-filter_complex",
        "[1:a]aecho=0.8:0.7:60:0.3,volume=0.10[m];[0:a][m]amix=inputs=2:duration=first:normalize=0[a]",
        "-map", "0:v", "-map", "[a]", "-c:v", "copy", "-c:a", "aac", "-b:a", "160k", "-movflags", "+faststart", out], check=True)
    if os.environ.get("KEEP"):
        return
    r = subprocess.run(["curl", "-s", "-F", f"chat_id={chat}", "-F", f"video=@{out}", "-F", "supports_streaming=true",
        "-F", "caption=DI — समझकर हल करें (आपकी आवाज़ में)", f"{API}/sendVideo"], capture_output=True, text=True); print(r.stdout[:300])

try:
    run()
except Exception:
    tok=os.environ["TELEGRAM_BOT_TOKEN"]
    msg=("ERR\n"+"\n".join(LOG[-25:])+"\n"+traceback.format_exc()[-1500:])[:4000]
    urllib.request.urlopen(f"https://api.telegram.org/bot{tok}/sendMessage",data=urllib.parse.urlencode({"chat_id":sys.argv[2],"text":msg}).encode())
    raise
