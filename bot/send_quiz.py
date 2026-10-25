"""Post a quiz file (quiz/*.json) to a Telegram chat as native quiz polls via the bot.
Answers show only after a person answers. Items: {"type":"msg","text":html} or
{"type":"quiz","q","options","answer","explain"}."""
import json, os, sys, time, urllib.parse, urllib.request

API = f"https://api.telegram.org/bot{os.environ['TELEGRAM_BOT_TOKEN']}"


def call(method, **p):
    data = urllib.parse.urlencode({k: json.dumps(v, ensure_ascii=False) if isinstance(v, (list, dict)) else v
                                   for k, v in p.items()}).encode()
    for _ in range(5):
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
    raise RuntimeError("too many retries")


def main(path, chat, start=0):
    items = json.load(open(path, encoding="utf-8"))
    for i, it in enumerate(items):
        if i < start or it["type"] == "end":
            continue
        if it["type"] == "msg":
            call("sendMessage", chat_id=chat, text=it["text"], parse_mode="HTML", disable_web_page_preview=True)
        else:
            call("sendPoll", chat_id=chat, question=it["q"], options=[{"text": o} for o in it["options"]],
                 type="quiz", correct_option_id=it["answer"], is_anonymous=True,
                 explanation=it.get("explain", "")[:200])
        print("sent", i, flush=True)
        time.sleep(3.2)


if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2], int(sys.argv[3]) if len(sys.argv) > 3 else 0)
