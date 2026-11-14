"""Phone Browser MCP — lets Claude drive the Exam Hub Browser Android app.
Phone app connects by WebSocket to /phone; Claude connects to /mcp (both behind the Caddy secret path)."""
import asyncio, base64, itertools, json, os
from mcp.server.fastmcp import FastMCP, Image
from starlette.routing import WebSocketRoute
from starlette.websockets import WebSocket, WebSocketDisconnect

PORT = int(os.environ.get("PHONE_MCP_PORT", "8767"))
mcp = FastMCP("Phone Browser", host="127.0.0.1", port=PORT, stateless_http=True, json_response=True)
mcp.settings.transport_security = None  # Caddy in front, secret path protects

STATE = {"ws": None, "info": {}}
PENDING: dict[int, asyncio.Future] = {}
IDS = itertools.count(1)

async def phone_ws(ws: WebSocket):
    await ws.accept()
    old = STATE["ws"]
    STATE["ws"] = ws
    if old:
        try: await old.close()
        except Exception: pass
    try:
        while True:
            msg = json.loads(await ws.receive_text())
            if msg.get("type") == "hello":
                STATE["info"] = msg; continue
            if msg.get("type") == "ping":
                await ws.send_text('{"type":"pong"}'); continue
            fut = PENDING.pop(msg.get("id"), None)
            if fut and not fut.done(): fut.set_result(msg)
    except (WebSocketDisconnect, RuntimeError, json.JSONDecodeError):
        pass
    finally:
        if STATE["ws"] is ws: STATE["ws"] = None

async def call(cmd: str, timeout: float = 40, **args):
    ws = STATE["ws"]
    if ws is None:
        raise RuntimeError("Phone app connected nahi hai — app kholo aur 'Claude ON' karo.")
    i = next(IDS); fut = asyncio.get_running_loop().create_future(); PENDING[i] = fut
    await ws.send_text(json.dumps({"id": i, "cmd": cmd, "args": args}, ensure_ascii=False))
    try:
        res = await asyncio.wait_for(fut, timeout)
    except asyncio.TimeoutError:
        PENDING.pop(i, None); raise RuntimeError(f"Phone ne {timeout:.0f}s mein jawab nahi diya ({cmd}).")
    if not res.get("ok"):
        raise RuntimeError(res.get("error") or "phone error")
    return res.get("result")

def _txt(r): return r if isinstance(r, str) else json.dumps(r, ensure_ascii=False, indent=1)

@mcp.tool()
async def phone_status() -> str:
    """Is the phone app connected? Returns device info, open tabs and the current page."""
    if STATE["ws"] is None: return "Phone connected nahi hai."
    return _txt({"connected": True, "device": STATE["info"], "tabs": await call("tabs")})

@mcp.tool()
async def open_url(url: str, new_tab: bool = False) -> str:
    """Open a URL (or search words) in the phone browser. Waits for the page to load."""
    return _txt(await call("open", 60, url=url, new_tab=new_tab))

@mcp.tool()
async def read_page(max_chars: int = 12000) -> str:
    """Visible text of the current page with its title and URL."""
    return _txt(await call("text", max_chars=max_chars))

@mcp.tool()
async def find_elements(filter: str = "") -> str:
    """List clickable/typeable elements on the page with an index number to use in click/type_text.
    filter: optional words to match in element text/label/name."""
    return _txt(await call("elements", filter=filter))

@mcp.tool()
async def click(index: int = -1, selector: str = "", x: float = -1, y: float = -1) -> str:
    """Click an element by index (from find_elements), CSS selector, or tap screen point x,y (0-1 fractions of the view)."""
    return _txt(await call("click", index=index, selector=selector, x=x, y=y))

@mcp.tool()
async def type_text(text: str, index: int = -1, selector: str = "", clear: bool = True, submit: bool = False) -> str:
    """Type into an input/textarea/select by index or selector. For <select>, text = option label or value. submit=True presses Enter."""
    return _txt(await call("type", text=text, index=index, selector=selector, clear=clear, submit=submit))

@mcp.tool()
async def scroll(direction: str = "down", amount: float = 0.8) -> str:
    """Scroll the page: direction up/down/top/bottom; amount = fraction of screen height."""
    return _txt(await call("scroll", direction=direction, amount=amount))

@mcp.tool()
async def screenshot(full_quality: bool = False) -> Image:
    """Screenshot of the phone browser view."""
    r = await call("screenshot", 40, hq=full_quality)
    return Image(data=base64.b64decode(r["jpeg"]), format="jpeg")

@mcp.tool()
async def navigate(action: str) -> str:
    """Browser history: back, forward or reload."""
    return _txt(await call("nav", 60, action=action))

@mcp.tool()
async def tabs(action: str = "list", tab: int = -1, url: str = "") -> str:
    """Manage tabs: list | switch (tab) | close (tab) | new (url)."""
    return _txt(await call("tabs", action=action, tab=tab, url=url))

@mcp.tool()
async def run_js(code: str) -> str:
    """Run JavaScript in the current page and return the result (JSON-serialised). Wrap multi-line code in an IIFE returning a value."""
    return _txt(await call("js", code=code))

@mcp.tool()
async def wait(seconds: float = 2, text: str = "") -> str:
    """Wait some seconds, or until the given text appears on the page (max 30s)."""
    return _txt(await call("wait", 45, seconds=seconds, text=text))

@mcp.tool()
async def show_message(text: str) -> str:
    """Show a short toast message on the phone (e.g. 'ab aap Submit dabayein')."""
    return _txt(await call("toast", text=text))

app = mcp.streamable_http_app()
app.router.routes.append(WebSocketRoute("/phone", phone_ws))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=PORT, ws_ping_interval=20, ws_ping_timeout=20)
