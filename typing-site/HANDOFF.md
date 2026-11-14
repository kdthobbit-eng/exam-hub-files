# HANDOFF — Alert Typing (xlstudy.in)

Ye file naye Claude account/session ke liye hai. Pehle poori padho, phir kaam shuru karo.
Last update: 14 Nov 2026.

## 1. User ke rules (zaroori)
- Jawab **chhote**, seedhe, **Hindi (Devanagari)** me. User Hinglish me likhta hai.
- Bada kaam banane se **pehle plan batao, user ke "yes" ka wait karo**. Chhote fix seedhe kar do.
- **Password kabhi mat daalo.** Hostinger / WordPress / Google login user khud karta hai.
- **Bot token** user khud WP admin me daalta hai, chat me kabhi mat maango.
- Design **AR Typing (artypingplatform.com) jaisa**, par **copy nahi**: AR ka text, passage, logo, code mat uthao.
- Exam ki tareekh khud se mat banao.
- Cron/scheduled job, DNS delete, DB bulk delete: pehle user se poochho.
- **Telegram:** post pehle sirf atul account ke **Saved Messages** ("me") me bhejo. Channel/group me tabhi jab user "yes" bole.
- Telegram message: **professional, kam shabd, sirf fact**. "AI jaisa" mat likho ("आसान", "मेल नहीं खाता", "बिल्कुल Free", bahut emoji: nahi).
- Admit card me personal data hota hai: sirf typing ke niyam use karo.
- ⚠️ VPS **14 Nov 2026** ko expire: user ko renew yaad dilao.

## 2. Project
- Site: **https://xlstudy.in** (brand **Alert Typing** / UPSSSC Alert, "By XL Study"). Purana domain erojgarresult.com → 301 redirect.
- Free typing test site: 129 exam, English + Hindi (Krutidev, Mangal Inscript, Remington Gail), exam jaisi window, result dashboard, Live Typing, 3284 section, Exam Hall, Download PDF, Insight, Typing History, Google login.
- Telegram: channel @upssscalertt, group @upssscalert, support contact **@TypingAlert**. Account "atul" (k tiwari) Telegram MCP me jud ta hai.
- User ke 3284 groups: "3284 आखिरी उम्मीद" @JA3280 (id -1004329143751), "3284 TYPING SKIP अभियान" @typingskip3284 (id -1004217768021), "3284 TYPING GROUP" (id -1004344915499).

## 3. Server
- Hostinger VPS, IP 72.60.192.158, Ubuntu 24.04, Caddy + PHP 8.3-FPM + MariaDB.
- WordPress: `/var/www/erojgarresult`, wp-cli: `wp --allow-root`.
- Terminal: hPanel → VPS → Web console.

## 4. Code aur deploy
- Repo **kdthobbit-eng/exam-hub-files**, theme: `typing-site/examhub-pro/`.
- Push: `cd typing-site && sh tools-push.sh "message"` → PHP lint, preview build, zip, commit, push.
  - Push reject ho (dusre session ka commit): `git pull --rebase`; conflict sirf `examhub-pro.zip` / `preview/index.html` me ho to `git checkout --theirs` + `git rebase --continue`, phir dobara push script chalao.
- **Live karna:** user WP admin → eh-settings → **Update theme now**, YA Telegram bot → Menu → **🔄 Update theme** (sirf owner). Push ke baad user ko yahi bolna hai.
- Local check: `php -l`, aur passage list ke liye chhota PHP harness (stub `get_option`, `wp_date`, `current_time`) jo `inc/data.php` require karke `eh_live_schedule('')` / `eh_live_schedule('3284')` / `eh_passage_days('en', 7)` print kare.

### Main files
- `inc/data.php` — passages load (`eh_passages()` = passages.json + DB option `eh_bot_passages`), `eh_live_schedule($want)` (Live list; `'3284'` = 3284 set), `eh_passage_days($lang)` (All Exams practice dates).
- `templates/live.php` — Live Typing + 3284 page (info tiles `.eh-3284-grid`, level chips, next-passage button, video card).
- `templates/_test-ui.php`, `assets/eh-typing.js` (engine), `assets/eh-eval.js` (mistakes + dashboard), `assets/eh-hindi.js` (Krutidev/Inscript maps).
- `templates/result.php` (result page), `templates/dashboard.php` (Typing History), `templates/results.php`.
- `functions.php` — `eh_result_verdict()`, `eh_row_speeds()` (history speeds = result page jaise).
- `inc/tgbot.php` — Telegram stats bot.
- `data/exams.json`, `data/passages.json` (indent=1). Passage generator scripts: `tools-news/*.py`.

## 5. Result ke niyam
- **UPSSSC** (`eval: upsssc`): words = keystrokes/5 (keys = screen par likhe akshar), errors = full + half/2, pehli 5 galti maaf, uske baad har galti par **6 shabd** kate (AR jaisa). Speed = net words / max(time, exam minutes).
- **UP Police SI/ASI/Computer Operator** (`eval: uppolice`): net WPM = (words − mistakes) / 15 min; Hindi 25, English 30 (ASI Accounts 15); 15% tak galti (85% accuracy). Poora passage khatam karna zaroori nahi.
- Baaki exams: target words/keys + allowed error %.
- Result page "Total Key Strokes" = screen par diya gaya text (live test agle passage me chala jata hai), kabhi typed keys se kam nahi.
- UP Police exams me word highlight band (`nohl: true`).

## 6. Passages — format aur niyam
- Field: `id` (sirf **chhote akshar**, `sanitize_key` capital ko small kar deta hai), `lang` en/hi, `title`, `text`, `words`, `chars`, `level` E/M/H/S, `day`, `both` (practice me bhi), `added` ("YYYY-MM-DD HH:MM", list me sabse upar), `tag` (3284 me label: "Evening Shift", "Easy"…).
- **Live Typing** sirf sabse nayi `day` ke passages dikhata hai (har din nayi list). `added` wale sabse upar; same time par English pehle, phir random level order.
- **3284 set**: `day: '3284'`, ids `x3284en..`/`x3284hi..`. Upar "Evening Shift"/"New"/tag wale.
- **All Exams**: `both: true` + `added` wale apni tareekh par sabse upar.
- **Hindi text me mana:** `"` `;` `%` `₹` latin akshar, curly quotes. Visarga (ः) theek hai (Mangal Shift+-, Krutidev %). 3284 Hindi me English ank (1947) aur "ई." chalta hai.
- **Duplicate check zaroori:** naye passage ko sab purane + aapas me 8-word shingles se milao; match mile to vaakya badlo. Purane topic dobara mat lo.
- User ka pasand: "thoda hard", levels Easy/Moderate/Hard **random order** me, aaj ki date me.

### Ab tak (Nov 2026)
- 11–13 Nov: news sets hx1/hx2/hx3, Super Hard (S), hx4 (5 Moderate, 13 Nov).
- 14 Nov: **hx5** = 120 Live (EN 60, HI 60) + **x3284enf/hif** = 30 (EN 15, HI 15).
- 3284 exam info (13 Nov): subah — English Netaji Subhas Bose, Hindi Deepawali, shabd Jewellery/Precious/Education/Agitation/01.10.1945, keyboard HP K100, 1 min demo, Alt+Shift language. Shaam — Hindi 1947 vibhajan, Lahore→Amritsar doctor (Moderate–Hard, "थे:" colon, डॉ॰/ई॰), English Indian Army (Medium). Passages "सभी शिफ्टों के पैटर्न व परीक्षा केंद्र से मिली समीक्षा पर आधारित".

## 7. Telegram bot (`inc/tgbot.php`)
- Webhook `/wp-json/eh/v1/tgbot` (secret header). Options: `eh_tgb_token`, `eh_tgb_secret`, `eh_tgb_admins`, `eh_tgb_ids`, `eh_tgb_join`. Bot: @Xlstudy_bot.
- Menu: Today, Last 1 hour, Yesterday, 7 days, Month, Full, Live Typing, Top exams, 🔴 3284 (count), Sign-ups, Admins.
- **📝 पैसेज जोड़ें / 📋 मेरे पैसेज** — sab admin: bhasha → level → title → paste → jhalak → ✅. DB option `eh_bot_passages` me jata hai (theme update se nahi mitta), Live + 3284 + All Exams me sabse upar. Admin sirf apne passage dekhe/hataye, owner sab.
- **➕ Add admin, 🔄 Update theme** (`eh_self_update()`), `/update` — sirf owner.
- Atul account se bot ke button `press_inline_button` se dabaye ja sakte hain (bot chat id 9132575238).

## 8. Baaki / aage
- Tablet hardware keyboard Hindi fix bana tha (commit f4495e0), user ne "abhi rahne do" bola — maange to wapas lagao.
- Home par "Upcoming Exams (4)" list khaali dikhi thi — user se confirm karna.
- Google Search Console me xlstudy.in + Change of address (baaki).
- Telegram MCP kabhi kabhi is chat me connect nahi hota — tab nayi chat ya user se screenshot.
