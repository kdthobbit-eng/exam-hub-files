#!/bin/bash
# Exam Hub phone-browser connector installer (VPS). Port 8767, Caddy secret path.
set -e
D=/opt/phone-mcp; mkdir -p $D
curl -fsSL https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/main/tools/phone-browser/server/server.py -o $D/server.py
[ -d $D/.venv ] || /root/.local/bin/uv venv -q $D/.venv
/root/.local/bin/uv pip install -q --python $D/.venv/bin/python "mcp<2" uvicorn websockets
printf '[Unit]\nDescription=Phone Browser MCP\nAfter=network-online.target\n[Service]\nWorkingDirectory=%s\nExecStart=%s/.venv/bin/python server.py\nRestart=always\nRestartSec=5\n[Install]\nWantedBy=multi-user.target\n' $D $D > /etc/systemd/system/phone-mcp.service
systemctl daemon-reload && systemctl enable -q phone-mcp && systemctl restart phone-mcp
if ! grep -q 8767 /etc/caddy/Caddyfile; then
  S=$(openssl rand -hex 20)
  cp /etc/caddy/Caddyfile /etc/caddy/Caddyfile.bak
  python3 - "$S" <<'PY'
import sys,re
s=sys.argv[1]; p='/etc/caddy/Caddyfile'; t=open(p).read()
block=f"\thandle_path /{s}/* {{\n\t\treverse_proxy 127.0.0.1:8767 {{\n\t\t\theader_up Host 127.0.0.1:8767\n\t\t}}\n\t}}\n"
i=t.rfind("\trespond 404"); t=t[:i]+block+t[i:]
open(p,'w').write(t)
PY
  echo "https://72-60-192-158.sslip.io/$S/mcp" > /root/phone-mcp-url.txt; chmod 600 /root/phone-mcp-url.txt
  caddy validate --config /etc/caddy/Caddyfile >/dev/null 2>&1 && systemctl reload caddy || { cp /etc/caddy/Caddyfile.bak /etc/caddy/Caddyfile; echo "Caddy error, purana config wapas"; exit 1; }
fi
sleep 4; systemctl is-active phone-mcp
U=$(cat /root/phone-mcp-url.txt)
echo "Claude connector link: $U"
echo "Phone app link:        ${U%/mcp}/phone"
