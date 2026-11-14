#!/bin/bash
# Exam Hub VPS connector installer. Port 8768, Caddy secret path.
set -e
D=/opt/vps-mcp; mkdir -p $D
curl -fsSL https://raw.githubusercontent.com/kdthobbit-eng/exam-hub-files/main/tools/vps-mcp/server.py -o $D/server.py
[ -d $D/.venv ] || /root/.local/bin/uv venv -q $D/.venv
/root/.local/bin/uv pip install -q --python $D/.venv/bin/python "mcp<2" uvicorn playwright
echo "Chrome install ho raha hai (2-3 min)..."
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq libatomic1 >/dev/null 2>&1 || true
$D/.venv/bin/python -m playwright install-deps chromium >/dev/null 2>&1 || true
$D/.venv/bin/python -m playwright install chromium >/dev/null 2>&1 || echo "Chrome install nahi hua — terminal tools phir bhi chalenge"
printf '[Unit]\nDescription=VPS MCP\nAfter=network-online.target\n[Service]\nWorkingDirectory=%s\nExecStart=%s/.venv/bin/python server.py\nRestart=always\nRestartSec=5\n[Install]\nWantedBy=multi-user.target\n' $D $D > /etc/systemd/system/vps-mcp.service
systemctl daemon-reload && systemctl enable -q vps-mcp && systemctl restart vps-mcp
if ! grep -q 8768 /etc/caddy/Caddyfile; then
  S=$(openssl rand -hex 20)
  cp /etc/caddy/Caddyfile /etc/caddy/Caddyfile.bak
  python3 - "$S" <<'PY'
import sys
s=sys.argv[1]; p='/etc/caddy/Caddyfile'; t=open(p).read()
block=f"\thandle_path /{s}/* {{\n\t\treverse_proxy 127.0.0.1:8768 {{\n\t\t\theader_up Host 127.0.0.1:8768\n\t\t}}\n\t}}\n"
i=t.rfind("\trespond 404"); t=t[:i]+block+t[i:]
open(p,'w').write(t)
PY
  echo "https://72-60-192-158.sslip.io/$S/mcp" > /root/vps-mcp-url.txt; chmod 600 /root/vps-mcp-url.txt
  caddy validate --config /etc/caddy/Caddyfile >/dev/null 2>&1 && systemctl reload caddy || { cp /etc/caddy/Caddyfile.bak /etc/caddy/Caddyfile; echo "Caddy error, purana config wapas"; exit 1; }
fi
sleep 4; systemctl is-active vps-mcp
echo "VPS connector link: $(cat /root/vps-mcp-url.txt)"
