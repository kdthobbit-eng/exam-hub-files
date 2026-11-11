#!/bin/sh
set -e
cd /home/claude/exam-hub-files/typing-site
for f in $(find examhub-pro -name '*.php'); do php -l $f >/dev/null || exit 1; done
for f in examhub-pro/assets/*.js; do node --check $f || exit 1; done
php preview/build.php >/dev/null
rm -f examhub-pro.zip && zip -qr examhub-pro.zip examhub-pro
cd .. && git add typing-site && git -c user.name="Claude" -c user.email="noreply@anthropic.com" commit -qm "${1:-Exam Hub Pro update}

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01QJdRFzQGZ9bUNAaXhrXFz1" && git fetch -q origin main && git rebase -q origin/main && git push -q origin HEAD:main && git log --oneline -1
