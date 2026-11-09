"""Gemini TTS: every line of <dir>/tts.json -> <dir>/audio/<id>.mp3, plus <dir>/durations.json.
usage: tts_gemini.py <dir> <voice> <style>   (env GEMINI_API_KEY)"""
import json, sys, os, time, base64, subprocess, urllib.request, urllib.error
from mutagen.mp3 import MP3

d, voice, style = sys.argv[1], sys.argv[2], sys.argv[3]
KEY = os.environ["GEMINI_API_KEY"]
MODELS = ["gemini-3.1-flash-tts-preview", "gemini-2.5-flash-preview-tts"]
items = json.load(open(f"{d}/tts.json", encoding="utf-8"))
os.makedirs(f"{d}/audio", exist_ok=True)

def call(model, text):
    body = {"contents": [{"parts": [{"text": f"{style}\n\n{text}"}]}],
            "generationConfig": {"responseModalities": ["AUDIO"],
                                 "speechConfig": {"voiceConfig": {"prebuiltVoiceConfig": {"voiceName": voice}}}}}
    req = urllib.request.Request(f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent",
                                 data=json.dumps(body).encode(), headers={"Content-Type": "application/json", "x-goog-api-key": KEY})
    with urllib.request.urlopen(req, timeout=120) as r:
        j = json.load(r)
    return base64.b64decode(j["candidates"][0]["content"]["parts"][0]["inlineData"]["data"])

model = None
for it in items:
    f = f"{d}/audio/{it['id']}.mp3"
    if os.path.exists(f):
        continue
    pcm = None
    for attempt in range(8):
        for m in ([model] if model else MODELS):
            try:
                pcm = call(m, it["text"]); model = m; break
            except urllib.error.HTTPError as e:
                msg = e.read().decode()[:300]
                print("HTTP", e.code, m, it["id"], msg)
                if e.code == 404: continue
                if e.code == 429: time.sleep(30 + 15 * attempt)
                else: time.sleep(5)
            except Exception as e:
                print("ERR", m, it["id"], e); time.sleep(5)
        if pcm: break
    if not pcm:
        print("stopping: could not synthesize", it["id"]); break
    subprocess.run(["ffmpeg", "-loglevel", "error", "-y", "-f", "s16le", "-ar", "24000", "-ac", "1", "-i", "-", "-b:a", "96k", f],
                   input=pcm, check=True)
    print("ok", it["id"], model)

done = [it for it in items if os.path.exists(f"{d}/audio/{it['id']}.mp3")]
json.dump({it["id"]: MP3(f"{d}/audio/{it['id']}.mp3").info.length for it in done}, open(f"{d}/durations.json", "w"), indent=0)
print("done", len(done), "of", len(items))
