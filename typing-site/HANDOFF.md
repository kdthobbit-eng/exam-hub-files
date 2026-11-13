# HANDOFF — Alert Typing (xlstudy.in)

Ye file naye Claude account/session ke liye hai. Pehle poori padho, phir kaam shuru karo.

## User ke rules (zaroori)
- Jawab **Hinglish** me, chhote aur seedhe.
- Koi bhi bada kaam banane se **pehle plan batao, user ke "yes" ka wait karo**.
- **Password kabhi mat daalo.** Hostinger / WordPress / Google login user khud karta hai.
- Site ka design **AR Typing Platform (artypingplatform.com) jaisa** ho, par **copy nahi**: AR ka text, passage, logo ya code mat uthao. Exam ke naam, target, time jaise facts apne shabdon me theek hain.
- Site ka screen text **simple English** me hai (user ne AI jaisi Hindi hatwayi thi). Blog aur info pages Hindi me hain.
- Exam ki tareekh khud se mat banao. Upcoming popup ki "confirmed" list user ke dene par hi bharo.
- Cron/scheduled job, DNS records delete karna, ya DB me bulk delete — pehle user se poochho.

## Project
- Website: **https://xlstudy.in** (brand: **Alert Typing**, logo "AT").
- Purana domain **erojgarresult.com** → 301 redirect to xlstudy.in (Caddy). Ise renew karte rehna hai.
- 100% free typing test site: 129 sarkari exam, English + Hindi (Krutidev, Mangal Inscript, Remington Gail), exam jaisi typing window, result dashboard, Live Typing, Exam Hall, Download PDF, Typing Insight, Typing History, Google login, Telegram links.
- Telegram: channel https://t.me/examhub26 , group https://t.me/examhub2026

## Server (Hostinger VPS KVM1)
- IP **72.60.192.158**, Ubuntu 24.04, Caddy + PHP 8.3-FPM + MariaDB.
- WordPress folder: `/var/www/erojgarresult` (folder ka naam purana hi hai, chalta hai).
- Caddy config: `/etc/caddy/Caddyfile` (xlstudy.in block + erojgarresult.com redirect block).
- Backups: `/root/backup/` (domain shift se pehle ka DB + files).
- wp-cli: hamesha `wp --allow-root`.
- Terminal: hPanel → VPS → **Web console** (user ka Chrome logged in hona chahiye).
- ⚠️ VPS **14 Nov 2026** ko expire hota hai — user ko renew yaad dilao.

## Code
- GitHub repo: **kdthobbit-eng/exam-hub-files**, theme folder: `typing-site/examhub-pro/`
- Deploy zip: `typing-site/examhub-pro.zip` (push script banata hai).
- Main files:
  - `front-page.php` — home page
  - `templates/_test-ui.php` — passage chunna, instructions, typing window, settings
  - `assets/eh-typing.js` — typing engine (timer, word highlight, backspace, submit)
  - `assets/eh-eval.js` — mistakes ka hisaab (full/half) + result dashboard
  - `assets/eh-hindi.js` — Krutidev / Inscript / Remington layouts, Unicode↔Krutidev
  - `inc/windows.php` — har exam family ki window size/font (AR se naapi hui)
  - `inc/data.php` — exams + passages, roz 6 passage
  - `data/exams.json` (129 exams), `data/passages.json` (138 passages)
  - `inc/auth.php` — login/signup + Google login (option `eh_google_client_id`)
  - `templates/` — live, examhall, download, insight, dashboard (history), results, exam, login, signup
  - `style.css` — saari CSS (naye blocks neeche jode gaye hain)
- Brand naam option `eh_brand` (default "Alert Typing" in `functions.php`).

## Deploy kaise karein
1. Code badlo, phir `tools-push.sh "message"` jaisa: PHP lint → zip → git commit → push.
2. VPS terminal me (commit SHA ke saath):
```
cd /var/www/erojgarresult; sh /root/eh-deploy.sh <SHA> && wp --allow-root option update eh_deployed_sha <SHA>
```
3. Live site par check karo.

## Google login
- Google Cloud project: **exam-hub-login**, OAuth client ID `89119738959-5tqbgu3q6engkpfncvagumajnpi2ab9j.apps.googleusercontent.com`
- Authorised origins me xlstudy.in, www.xlstudy.in (aur purane erojgarresult.com) hain.
- Branding naam "Alert Typing" save hai; Google verification baaki hai (tab tak login chalta hai, naam nahi dikhta).

## Ho chuka (Nov 2026)
- Sab typing sections AR se milaye: exam page, demo, result dashboard, live, exam hall, download (Krutidev/Inscript/Remington PDF), insight.
- 56 exam window families ke size AR jaise.
- Home page naya (AR jaisa saaf, English).
- Brand Exam Hub → Alert Typing; domain erojgarresult.com → xlstudy.in.

## Baaki / aage ke kaam
- Google Search Console me xlstudy.in jodna + "Change of address".
- Google branding verification (optional).
- Upcoming exams ki pakki tareekh user se lekar popup me daalna.
- Telegram channel/group ka naam/link badalna ho to user batayega.

- Live Typing me "Super Hard" level (code S): 25 passage (20 EN + 5 HI, day 2026-11-13), list me sabse upar. Hindi passages me sirf Krutidev-safe chinh (no quotes, colon, latin).
