"""TTS every line of <dir>/tts.json into <dir>/audio/<id>.mp3 and write <dir>/durations.json."""
import json, sys, asyncio, os
import edge_tts
from mutagen.mp3 import MP3
d, voice, rate = sys.argv[1], sys.argv[2], sys.argv[3]
items = json.load(open(f"{d}/tts.json", encoding="utf-8"))
os.makedirs(f"{d}/audio", exist_ok=True)
async def one(it):
    f = f"{d}/audio/{it['id']}.mp3"
    for k in range(4):
        try:
            await edge_tts.Communicate(it["text"], voice, rate=rate).save(f); return
        except Exception as e:
            print("retry", it["id"], e); await asyncio.sleep(2)
    raise SystemExit("tts failed " + it["id"])
async def main():
    sem = asyncio.Semaphore(6)
    async def g(it):
        async with sem: await one(it)
    await asyncio.gather(*(g(it) for it in items))
asyncio.run(main())
json.dump({it["id"]: MP3(f"{d}/audio/{it['id']}.mp3").info.length for it in items}, open(f"{d}/durations.json", "w"), indent=0)
print("done", len(items))
