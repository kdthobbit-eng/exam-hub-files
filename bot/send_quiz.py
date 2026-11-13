"""Post a quiz file (quiz/*.json) to a Telegram chat as native quiz polls via the bot.
Answers show only after a person answers. Items: {"type":"msg","text":html} or
{"type":"quiz","q","options","answer","explain"}."""
import json, os, sys, time, urllib.parse, urllib.request

API = f"https://api.telegram.org/bot{os.environ['TELEGRAM_BOT_TOKEN']}"


def call(method, **p):
    data = urllib.parse.urlencode({k: json.dumps(v, ensure_ascii=False) if isinstance(v, (list, dict)) else v
                                   for k, v in p.items()}).encode()
    for _ in range(10):
        try:
            with urllib.request.urlopen(f"{API}/{method}", data=data, timeout=60) as r:
                return json.loads(r.read())
        except urllib.error.HTTPError as e:
            body = json.loads(e.read() or b"{}")
            wait = (body.get("parameters") or {}).get("retry_after")
            if wait:
                time.sleep(wait + 1)
                continue
            raise RuntimeError(f"{method}: {body}")
        except (urllib.error.URLError, ConnectionError, TimeoutError) as e:
            print("net retry:", e, flush=True)
            time.sleep(5)
    raise RuntimeError("too many retries")


def main(path, chat, start=0, thread=None):
    extra = {"message_thread_id": int(thread)} if thread else {}
    items = json.load(open(path, encoding="utf-8"))
    for i, it in enumerate(items):
        if i < start or it["type"] == "end":
            continue
        if it["type"] == "photo":
            call("sendPhoto", **extra, chat_id=chat, photo=it["url"], caption=it.get("caption", ""), parse_mode="HTML")
        elif it["type"] == "msg":
            call("sendMessage", **extra, chat_id=chat, text=it["text"], parse_mode="HTML", disable_web_page_preview=True)
        else:
            call("sendPoll", **extra, chat_id=chat, question=it["q"], options=[{"text": o} for o in it["options"]],
                 type="quiz", correct_option_id=it["answer"], is_anonymous=True,
                 explanation=it.get("explain", "")[:200])
        print("sent", i, flush=True)
        time.sleep(float(os.environ.get("QUIZ_SLEEP","3.2")))


if __name__ == "__main__":
    try:
        main(sys.argv[1], sys.argv[2], int(sys.argv[3]) if len(sys.argv) > 3 else 0,
             sys.argv[4] if len(sys.argv) > 4 else None)
    except Exception as e:
        print(f"::error::{str(e)[:500]}")
        sys.exit(1)
