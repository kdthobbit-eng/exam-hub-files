"""Build a narrated video from video/<dir>/segments.json (img + Hindi text + pause) and send it via the bot."""
import json, os, subprocess, sys, asyncio, urllib.request
import edge_tts, imageio_ffmpeg
from mutagen.mp3 import MP3
FF=imageio_ffmpeg.get_ffmpeg_exe()
d, chat, voice = sys.argv[1], sys.argv[2], (sys.argv[3] if len(sys.argv) > 3 else "hi-IN-SwaraNeural")
segs = json.load(open(f"{d}/segments.json", encoding="utf-8"))
META = json.load(open(f"{d}/meta.json", encoding="utf-8")) if os.path.exists(f"{d}/meta.json") else {}
RATE = META.get("rate", "-5%")
def dur(f): return MP3(f).info.length
async def tts(t, f): await edge_tts.Communicate(t, voice, rate=RATE).save(f)
clips=[]
FPS=str(META.get("fps",25))
for i,s in enumerate(segs):
    a=f"/tmp/a{i}.mp3"; asyncio.run(tts(s["text"],a))
    t=dur(a)+0.6+float(s.get("pause",0)); c=f"/tmp/c{i}.mp4"
    if s.get("intro") and s.get("loop"):
        # animated intro clip (1.2 s) followed by a seamless moving-mechanism loop, trimmed to the narration
        t=max(dur(a)+0.35+float(s.get("pause",0)),1.3); rem=t-1.2
        subprocess.run([FF,"-y","-loglevel","error","-i",f"{d}/{s['intro']}","-stream_loop","-1","-i",f"{d}/{s['loop']}","-i",a,
            "-filter_complex",f"[0:v]format=yuv420p,setsar=1[i];[1:v]trim=duration={rem:.2f},setpts=PTS-STARTPTS,format=yuv420p,setsar=1[s];[i][s]concat=n=2:v=1:a=0[v];[2:a]apad[a]",
            "-map","[v]","-map","[a]","-t",f"{t:.2f}","-c:v","libx264","-preset","veryfast","-pix_fmt","yuv420p","-r",FPS,
            "-c:a","aac","-b:a","128k","-ar","44100","-ac","2",c],check=True)
    elif s.get("intro"):
        # animated intro clip (1.2 s) followed by the held final frame, narration from t=0
        t=max(dur(a)+0.35+float(s.get("pause",0)),1.3); rem=t-1.2
        subprocess.run([FF,"-y","-loglevel","error","-i",f"{d}/{s['intro']}","-loop","1","-framerate",FPS,"-t",f"{rem:.2f}","-i",f"{d}/{s['img']}","-i",a,
            "-filter_complex","[0:v]format=yuv420p,setsar=1[i];[1:v]format=yuv420p,setsar=1[s];[i][s]concat=n=2:v=1:a=0[v];[2:a]apad[a]",
            "-map","[v]","-map","[a]","-t",f"{t:.2f}","-c:v","libx264","-preset","veryfast","-pix_fmt","yuv420p","-r",FPS,
            "-c:a","aac","-b:a","128k","-ar","44100","-ac","2",c],check=True)
    else:
        subprocess.run([FF,"-y","-loglevel","error","-loop","1","-i",f"{d}/{s['img']}","-i",a,"-af",f"apad","-t",f"{t:.2f}",
            "-c:v","libx264","-preset","veryfast","-tune","stillimage","-pix_fmt","yuv420p","-r",FPS,"-c:a","aac","-b:a","128k","-ar","44100","-ac","2",c],check=True)
    clips.append(c)
open("/tmp/list.txt","w").write("".join(f"file '{c}'\n" for c in clips))
out=f"/tmp/{os.path.basename(d)}.mp4"
subprocess.run([FF,"-y","-loglevel","error","-f","concat","-safe","0","-i","/tmp/list.txt","-c","copy","-movflags","+faststart",out],check=True)
print("size",os.path.getsize(out))
tok=os.environ["TELEGRAM_BOT_TOKEN"]
r=subprocess.run(["curl","-s","-F",f"chat_id={chat}","-F",f"video=@{out}","-F","supports_streaming=true","-F","caption="+META.get("caption","DI — समझकर हल करें (Sample)"),
    f"https://api.telegram.org/bot{tok}/sendVideo"],capture_output=True,text=True); print(r.stdout[:300])
