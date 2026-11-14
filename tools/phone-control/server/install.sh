#!/bin/bash
# Exam Hub Phone (pura phone) connector installer (VPS). Port 8768, Caddy secret path.
set -e
D=/opt/phone-ctl; mkdir -p $D/files
curl -fsSL https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/main/tools/phone-control/server/server.py -o $D/server.py
[ -d $D/.venv ] || /root/.local/bin/uv venv -q $D/.venv
/root/.local/bin/uv pip install -q --python $D/.venv/bin/python "mcp<2" uvicorn websockets pillow pypdf
if ! grep -q 8768 /etc/caddy/Caddyfile; then
  S=$(openssl rand -hex 20)
  cp /etc/caddy/Caddyfile /etc/caddy/Caddyfile.bak
  python3 - "$S" <<'PY'
import sys
s=sys.argv[1]; p='/etc/caddy/Caddyfile'; t=open(p).read()
block=f"\thandle_path /{s}/* {{\n\t\trequest_body {{\n\t\t\tmax_size 200MB\n\t\t}}\n\t\treverse_proxy 127.0.0.1:8768 {{\n\t\t\theader_up Host 127.0.0.1:8768\n\t\t}}\n\t}}\n"
i=t.rfind("\trespond 404"); t=t[:i]+block+t[i:]
open(p,'w').write(t)
PY
  echo "https://72-60-192-158.sslip.io/$S" > /root/phone-ctl-base.txt; chmod 600 /root/phone-ctl-base.txt
  caddy validate --config /etc/caddy/Caddyfile >/dev/null 2>&1 && systemctl reload caddy || { cp /etc/caddy/Caddyfile.bak /etc/caddy/Caddyfile; rm -f /root/phone-ctl-base.txt; echo "Caddy error, purana config wapas"; exit 1; }
fi
B=$(cat /root/phone-ctl-base.txt)
printf '[Unit]\nDescription=Phone Control MCP\nAfter=network-online.target\n[Service]\nWorkingDirectory=%s\nEnvironment=PHONE_CTL_BASE=%s\nExecStart=%s/.venv/bin/python server.py\nRestart=always\nRestartSec=5\n[Install]\nWantedBy=multi-user.target\n' $D "$B" $D > /etc/systemd/system/phone-ctl.service
systemctl daemon-reload && systemctl enable -q phone-ctl && systemctl restart phone-ctl
# purani files 7 din baad apne aap delete
( crontab -l 2>/dev/null | grep -v phone-ctl/files; echo "0 3 * * * find /opt/phone-ctl/files -type f -mtime +7 -delete" ) | crontab -
sleep 4; systemctl is-active phone-ctl
echo "Claude connector link: $B/mcp"
echo "Phone app link:        $B/device"
