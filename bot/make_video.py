"""Build a narrated video from video/<dir>/segments.json (img + Hindi text + pause) and send it via the bot."""
import json, os, subprocess, sys, asyncio, urllib.request
import edge_tts
d, chat, voice = sys.argv[1], sys.argv[2], (sys.argv[3] if len(sys.argv) > 3 else "hi-IN-SwaraNeural")
segs = json.load(open(f"{d}/segments.json", encoding="utf-8"))
def dur(f): return float(subprocess.check_output(["ffprobe","-v","error","-show_entries","format=duration","-of","csv=p=0",f]).decode())
async def tts(t, f): await edge_tts.Communicate(t, voice, rate="-5%").save(f)
clips=[]
for i,s in enumerate(segs):
    a=f"/tmp/a{i}.mp3"; asyncio.run(tts(s["text"],a))
    t=dur(a)+0.6+float(s.get("pause",0)); c=f"/tmp/c{i}.mp4"
    subprocess.run(["ffmpeg","-y","-loglevel","error","-loop","1","-i",f"{d}/{s['img']}","-i",a,"-af",f"apad","-t",f"{t:.2f}",
        "-c:v","libx264","-preset","veryfast","-tune","stillimage","-pix_fmt","yuv420p","-r","25","-c:a","aac","-b:a","128k","-ar","44100","-ac","2",c],check=True)
    clips.append(c)
open("/tmp/list.txt","w").write("".join(f"file '{c}'\n" for c in clips))
out=f"/tmp/{os.path.basename(d)}.mp4"
subprocess.run(["ffmpeg","-y","-loglevel","error","-f","concat","-safe","0","-i","/tmp/list.txt","-c","copy","-movflags","+faststart",out],check=True)
print("duration",dur(out),"size",os.path.getsize(out))
tok=os.environ["TELEGRAM_BOT_TOKEN"]
r=subprocess.run(["curl","-s","-F",f"chat_id={chat}","-F",f"video=@{out}","-F","supports_streaming=true","-F","caption=DI — समझकर हल करें (Sample)",
    f"https://api.telegram.org/bot{tok}/sendVideo"],capture_output=True,text=True); print(r.stdout[:300])
