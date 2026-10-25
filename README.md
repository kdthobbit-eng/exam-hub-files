# Exam Hub — website, files and content bot

- **Website:** https://kdthobbit-eng.github.io/exam-hub-files/ (also shown on erojgarresult.com/exam-hub/).
  Same Firebase as the Exam Hub Android app — tests, quizzes, PDFs and logins are shared.
- **files/** — PDFs and photos, served free through jsDelivr.
- **Telegram bot** (`bot/telegram_bot.py`) — send a PDF, photo, quiz or update to the bot with tags like
  `#UPSSSC #PDF`; it appears in the app and on the website within ~10 minutes.
- **inbox/** — drop a JSON file here (Claude does this from chat) to publish content. See `bot/inbox.py`.

Secrets (Settings → Secrets and variables → Actions):
`TELEGRAM_BOT_TOKEN`, `FIREBASE_SERVICE_ACCOUNT`, and optionally `ADMIN_TELEGRAM_IDS` (comma-separated).
