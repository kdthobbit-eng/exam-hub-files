"""VPS MCP — terminal, files and a headless Chrome on the VPS for Claude. Port 8768, behind Caddy secret path."""
import asyncio, base64, os, re, shutil
from mcp.server.fastmcp import FastMCP, Image

PORT = int(os.environ.get("VPS_MCP_PORT", "8768"))
mcp = FastMCP("VPS", host="127.0.0.1", port=PORT, stateless_http=True, json_response=True)
mcp.settings.transport_security = None

BLOCK = [r"\brm\s+(-[a-zA-Z]*\s+)*-?[a-zA-Z]*r[a-zA-Z]*f?\s+/(\s|$|\*)", r"\bmkfs", r"\bdd\b.*\bof=/dev/", r"\b(shutdown|poweroff|halt|reboot|init\s+0)\b",
         r":\(\)\s*\{", r">\s*/dev/sd", r"\bchmod\s+-R\s+[0-7]+\s+/(\s|$)", r"\bchown\s+-R\s+\S+\s+/(\s|$)", r"\buserdel\s+root", r"\bpasswd\b",
         r"/etc/caddy/Caddyfile", r"(phone|yt|vps)-mcp-url\.txt|mcp-url\.txt"]

def _blocked(cmd):
    return next((p for p in BLOCK if re.search(p, cmd)), None)

@mcp.tool()
async def run(command: str, timeout: int = 120, cwd: str = "/root") -> str:
    """Run a bash command on the VPS (as root). Returns exit code + output (last 20k chars). Destructive commands are blocked."""
    p = _blocked(command)
    if p: return f"BLOCKED (safety rule): {p}"
    proc = await asyncio.create_subprocess_shell(command, cwd=cwd, stdout=asyncio.subprocess.PIPE, stderr=asyncio.subprocess.STDOUT,
                                                 executable="/bin/bash")
    try:
        out, _ = await asyncio.wait_for(proc.communicate(), timeout=min(timeout, 1800))
    except asyncio.TimeoutError:
        proc.kill(); return f"TIMEOUT after {timeout}s"
    t = out.decode(errors="replace")
    return f"exit={proc.returncode}\n" + (t[-20000:] if len(t) > 20000 else t)

@mcp.tool()
def read_file(path: str, max_chars: int = 20000, offset: int = 0) -> str:
    """Read a text file from the VPS."""
    if _blocked(path): return "BLOCKED"
    with open(path, encoding="utf-8", errors="replace") as f:
        f.seek(0); t = f.read()
    return t[offset:offset + max_chars] + (f"\n…[{len(t)} chars total]" if len(t) > offset + max_chars else "")

@mcp.tool()
def write_file(path: str, content: str, append: bool = False) -> str:
    """Create/overwrite (or append to) a text file on the VPS."""
    if _blocked(path): return "BLOCKED"
    os.makedirs(os.path.dirname(os.path.abspath(path)), exist_ok=True)
    with open(path, "a" if append else "w", encoding="utf-8") as f: f.write(content)
    return f"wrote {len(content)} chars to {path}"

@mcp.tool()
def list_dir(path: str = "/root") -> str:
    """List a folder (name, size, type)."""
    out = []
    for e in sorted(os.scandir(path), key=lambda e: e.name):
        try: out.append(f"{'d' if e.is_dir() else '-'} {e.stat().st_size:>10}  {e.name}")
        except OSError: out.append(f"? {e.name}")
    return "\n".join(out) or "(empty)"

@mcp.tool()
async def status() -> str:
    """VPS health: uptime, CPU load, RAM, disk and connector services."""
    return await run("uptime; free -h; df -h / | tail -1; for s in caddy telegram-mcp yt-mcp phone-mcp vps-mcp; do printf '%-14s %s\\n' $s $(systemctl is-active $s); done", 30)

# ---------------- headless Chrome ----------------
_B = {"pw": None, "browser": None, "page": None}

async def _page():
    if _B["page"] is None or _B["page"].is_closed():
        from playwright.async_api import async_playwright
        if _B["pw"] is None: _B["pw"] = await async_playwright().start()
        if _B["browser"] is None:
            _B["browser"] = await _B["pw"].chromium.launch_persistent_context(
                "/opt/vps-mcp/chrome-profile", headless=True, viewport={"width": 1280, "height": 800}, locale="en-IN",
                args=["--no-sandbox", "--disable-dev-shm-usage"])
        _B["page"] = _B["browser"].pages[0] if _B["browser"].pages else await _B["browser"].new_page()
    return _B["page"]

@mcp.tool()
async def browser_open(url: str) -> str:
    """Open a URL in the VPS's own headless Chrome."""
    p = await _page()
    if not re.match(r"^[a-z]+://", url): url = "https://" + url
    await p.goto(url, wait_until="domcontentloaded", timeout=60000)
    return f"{p.url}\n{await p.title()}"

@mcp.tool()
async def browser_text(max_chars: int = 12000) -> str:
    """Visible text of the VPS browser page."""
    p = await _page()
    t = await p.inner_text("body")
    return f"{p.url}\n{await p.title()}\n\n{t[:max_chars]}"

@mcp.tool()
async def browser_screenshot(full_page: bool = False) -> Image:
    """Screenshot of the VPS browser."""
    p = await _page()
    return Image(data=await p.screenshot(type="jpeg", quality=70, full_page=full_page), format="jpeg")

@mcp.tool()
async def browser_click(selector: str = "", text: str = "") -> str:
    """Click by CSS selector or by visible text."""
    p = await _page()
    loc = p.get_by_text(text, exact=False).first if text else p.locator(selector).first
    await loc.click(timeout=15000)
    await p.wait_for_load_state("domcontentloaded")
    return f"clicked → {p.url}"

@mcp.tool()
async def browser_type(selector: str, text: str, submit: bool = False) -> str:
    """Fill an input (CSS selector). submit=True presses Enter."""
    p = await _page()
    await p.fill(selector, text, timeout=15000)
    if submit: await p.press(selector, "Enter")
    return "typed"

@mcp.tool()
async def browser_js(code: str) -> str:
    """Run JavaScript in the VPS browser page (expression or arrow function)."""
    p = await _page()
    return str(await p.evaluate(code))

app = mcp.streamable_http_app()

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=PORT)
