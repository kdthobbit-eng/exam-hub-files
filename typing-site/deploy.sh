#!/bin/sh
# Exam Hub Pro deploy: VPS par chalayen — sh deploy.sh
set -e
T=/var/www/erojgarresult/wp-content/themes
cd /tmp && rm -rf ehp && mkdir ehp && cd ehp
SHA=${1:-$(curl -sSf https://api.github.com/repos/kdthobbit-eng/exam-hub-files/commits/main | grep -m1 '"sha"' | cut -d'"' -f4)}
echo "commit $SHA"
curl -sSfLo t.zip "https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/$SHA/typing-site/examhub-pro.zip"
unzip -q t.zip
rm -rf $T/examhub-pro.new && mv examhub-pro $T/examhub-pro.new
rm -rf $T/examhub-pro.old; [ -d $T/examhub-pro ] && mv $T/examhub-pro $T/examhub-pro.old
mv $T/examhub-pro.new $T/examhub-pro
chown -R www-data:www-data $T/examhub-pro
echo DEPLOYED $(date)
