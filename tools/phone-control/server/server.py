"""Phone Control MCP — lets Claude drive the whole Android phone (all apps, storage,
notifications, WhatsApp) through the 'Exam Hub Phone' app (AccessibilityService).
Phone connects by WebSocket to /device; Claude connects to /mcp; phone uploads files to /upload.
All routes sit behind the Caddy secret path."""
import asyncio, base64, io, itertools, json, os, re, secrets, time
from mcp.server.fastmcp import FastMCP, Image
from starlette.requests import Request
from starlette.responses import FileResponse, JSONResponse, PlainTextResponse
from starlette.routing import Route, WebSocketRoute
from starlette.websockets import WebSocket, WebSocketDisconnect

PORT = int(os.environ.get("PHONE_CTL_PORT", "8768"))
FILES = os.environ.get("PHONE_CTL_FILES", "/opt/phone-ctl/files")
BASE_URL = os.environ.get("PHONE_CTL_BASE", "")  # e.g. https://x.sslip.io/<secret>
os.makedirs(FILES, exist_ok=True)

mcp = FastMCP("Phone Control", host="127.0.0.1", port=PORT, stateless_http=True, json_response=True)
mcp.settings.transport_security = None  # Caddy in front, secret path protects

STATE = {"ws": None, "info": {}}
PENDING: dict[int, asyncio.Future] = {}
UPLOADS: dict[str, asyncio.Future] = {}
IDS = itertools.count(1)


async def device_ws(ws: WebSocket):
    await ws.accept()
    old, STATE["ws"] = STATE["ws"], ws
    if old:
        try: await old.close()
        except Exception: pass
    try:
        while True:
            msg = json.loads(await ws.receive_text())
            t = msg.get("type")
            if t == "hello": STATE["info"] = msg; continue
            if t == "ping": await ws.send_text('{"type":"pong"}'); continue
            fut = PENDING.pop(msg.get("id"), None)
            if fut and not fut.done(): fut.set_result(msg)
    except (WebSocketDisconnect, RuntimeError, json.JSONDecodeError):
        pass
    finally:
        if STATE["ws"] is ws: STATE["ws"] = None


async def upload(request: Request):
    tok = request.query_params.get("t", "")
    fut = UPLOADS.get(tok)
    if fut is None: return PlainTextResponse("bad token", 403)
    name = re.sub(r"[^\w.\- ]", "_", request.query_params.get("name", "file"))[-120:]
    path = os.path.join(FILES, f"{int(time.time())}_{name}")
    with open(path, "wb") as f:
        async for chunk in request.stream(): f.write(chunk)
    if not fut.done(): fut.set_result(path)
    return JSONResponse({"ok": True})


async def files(request: Request):
    p = os.path.join(FILES, os.path.basename(request.path_params["name"]))
    return FileResponse(p) if os.path.isfile(p) else PlainTextResponse("not found", 404)


async def call(cmd: str, timeout: float = 40, **args):
    ws = STATE["ws"]
    if ws is None:
        raise RuntimeError("Phone connected nahi hai — 'Exam Hub Phone' app kholo, Accessibility ON hai ya nahi dekho.")
    i = next(IDS); fut = asyncio.get_running_loop().create_future(); PENDING[i] = fut
    await ws.send_text(json.dumps({"id": i, "cmd": cmd, "args": args}, ensure_ascii=False))
    try:
        res = await asyncio.wait_for(fut, timeout)
    except asyncio.TimeoutError:
        PENDING.pop(i, None); raise RuntimeError(f"Phone ne {timeout:.0f}s mein jawab nahi diya ({cmd}).")
    if not res.get("ok"): raise RuntimeError(res.get("error") or "phone error")
    return res.get("result")


def _txt(r): return r if isinstance(r, str) else json.dumps(r, ensure_ascii=False, indent=1)


# ---------------- status & screen ----------------
@mcp.tool()
async def device_status() -> str:
    """Is the phone connected? Device model, Android version, battery, app version, which permissions are on, and the app currently on screen."""
    if STATE["ws"] is None: return "Phone connected nahi hai."
    return _txt({"connected": True, "device": STATE["info"], "now": await call("status")})


@mcp.tool()
async def screen_read(filter: str = "", max_items: int = 150) -> str:
    """Read what is on the phone screen right now (any app): the current app plus a numbered list of
    texts, buttons and input boxes. Use the [number] with tap / type_text. CHEAPER than screenshot —
    always try this first. filter: only items containing these words."""
    return _txt(await call("read", filter=filter, max_items=max_items))


@mcp.tool()
async def screenshot(full_quality: bool = False) -> Image:
    """Picture of the whole phone screen (Android 11+). Use only when screen_read is not enough
    (images, games, unlabelled icons). For tap(x,y) use fractions: x = px/width, y = px/height."""
    r = await call("screenshot", 30, hq=full_quality)
    return Image(data=base64.b64decode(r["jpeg"]), format="jpeg")


# ---------------- actions ----------------
@mcp.tool()
async def tap(index: int = -1, x: float = -1, y: float = -1, long_press: bool = False) -> str:
    """Tap an item by its [index] from screen_read, or a screen point x,y given as 0-1 fractions.
    long_press=True for press-and-hold. Returns the new screen app/title."""
    return _txt(await call("tap", index=index, x=x, y=y, long=long_press))


@mcp.tool()
async def type_text(text: str, index: int = -1, clear: bool = True, submit: bool = False) -> str:
    """Type text into an input box: index from screen_read, or -1 for the box that has focus.
    clear=False appends. submit=True presses the keyboard's Enter/Search/Go."""
    return _txt(await call("type", text=text, index=index, clear=clear, submit=submit))


@mcp.tool()
async def swipe(direction: str = "up", amount: float = 0.6, x1: float = -1, y1: float = -1,
                x2: float = -1, y2: float = -1, duration_ms: int = 350) -> str:
    """Swipe the screen. direction up/down/left/right ('up' = finger moves up = content scrolls down),
    amount = fraction of screen. Or give exact x1,y1 -> x2,y2 (0-1 fractions)."""
    return _txt(await call("swipe", direction=direction, amount=amount, x1=x1, y1=y1, x2=x2, y2=y2, ms=duration_ms))


@mcp.tool()
async def press(key: str) -> str:
    """Press a system key: back | home | recents | notifications | quick_settings | lock | power_menu | split_screen."""
    return _txt(await call("press", key=key))


@mcp.tool()
async def wait(seconds: float = 2, text: str = "") -> str:
    """Wait some seconds, or until the given text appears on screen (max 30s)."""
    return _txt(await call("wait", 45, seconds=seconds, text=text))


# ---------------- apps ----------------
@mcp.tool()
async def list_apps(filter: str = "") -> str:
    """Installed apps (name + package). filter: words to match."""
    return _txt(await call("apps", filter=filter))


@mcp.tool()
async def open_app(name: str) -> str:
    """Open an app by its name (e.g. 'WhatsApp', 'YouTube', 'Settings') or package name."""
    return _txt(await call("open_app", 20, name=name))


@mcp.tool()
async def open_link(url: str, package: str = "") -> str:
    """Open a link/deep link with the phone (tel:, mailto:, https:, upi: etc.). package: force a specific app."""
    return _txt(await call("open_link", 20, url=url, package=package))


@mcp.tool()
async def send_whatsapp(to: str, text: str, send: bool = True, business: bool = False) -> str:
    """Send a WhatsApp message. to = phone number (10 digits = India) OR a contact name from the phone's contacts.
    send=False only opens the chat with the text typed. business=True uses WhatsApp Business."""
    return _txt(await call("whatsapp", 40, to=to, text=text, send=send, business=business))


@mcp.tool()
async def contacts_search(query: str) -> str:
    """Search the phone's contacts by name or number."""
    return _txt(await call("contacts", query=query))


@mcp.tool()
async def notifications(limit: int = 20, app: str = "") -> str:
    """Recent notifications on the phone (newest first): app, title, text, time. app: filter by app name/package."""
    return _txt(await call("notifs", limit=limit, app=app))


@mcp.tool()
async def show_message(text: str) -> str:
    """Show a short toast message on the phone."""
    return _txt(await call("toast", text=text))


# ---------------- storage ----------------
@mcp.tool()
async def latest_files(type: str = "", query: str = "", folder: str = "", limit: int = 20, days: int = 0) -> str:
    """Newest files in phone storage. type: pdf | image | video | audio | doc | apk | any extension like 'xlsx'.
    query: words in the file name. folder: e.g. 'Download', 'WhatsApp', 'DCIM' (default all). days: only last N days."""
    return _txt(await call("latest", 90, type=type, query=query, folder=folder, limit=limit, days=days))


@mcp.tool()
async def list_folder(path: str = "") -> str:
    """List a folder in phone storage ('' = storage root, or e.g. 'Download')."""
    return _txt(await call("ls", path=path))


@mcp.tool()
async def get_file(path: str, max_chars: int = 15000):
    """Bring a phone file here. Images come back as a picture, PDFs/text as their text, plus a download link."""
    tok = secrets.token_urlsafe(16)
    fut = asyncio.get_running_loop().create_future(); UPLOADS[tok] = fut
    try:
        await call("upload", 30, path=path, token=tok)
        saved = await asyncio.wait_for(fut, 300)
    finally:
        UPLOADS.pop(tok, None)
    name = os.path.basename(saved); size = os.path.getsize(saved)
    link = f"{BASE_URL}/files/{name}" if BASE_URL else f"(server file {saved})"
    low = name.lower()
    if low.endswith((".jpg", ".jpeg", ".png", ".webp")):
        from PIL import Image as PI
        im = PI.open(saved).convert("RGB"); im.thumbnail((1600, 1600))
        b = io.BytesIO(); im.save(b, "JPEG", quality=80)
        return [Image(data=b.getvalue(), format="jpeg"), f"{name} ({size//1024} KB) — {link}"]
    head = f"File: {name} ({size//1024} KB)\nLink: {link}\n\n"
    if low.endswith(".pdf"):
        try:
            from pypdf import PdfReader
            r = PdfReader(saved); parts = []
            for i, p in enumerate(r.pages):
                parts.append(f"--- page {i+1} ---\n{p.extract_text() or ''}")
                if sum(map(len, parts)) > max_chars: break
            t = "\n".join(parts)[:max_chars]
            return head + f"Pages: {len(r.pages)}\n" + (t.strip() or "(PDF me text nahi — scanned lagti hai)")
        except Exception as e:
            return head + f"PDF padh nahi paya: {e}"
    if low.endswith((".txt", ".csv", ".json", ".html", ".md", ".xml", ".log")):
        return head + open(saved, encoding="utf-8", errors="replace").read(max_chars)
    return head + "(ye file type yahan text me nahi dikh sakta — link se download karo)"


@mcp.tool()
async def download_to_phone(url: str, filename: str = "") -> str:
    """Download a file from a URL into the phone's Download folder."""
    return _txt(await call("download", 300, url=url, filename=filename))


@mcp.tool()
async def share_file(path: str, app: str = "com.whatsapp", text: str = "") -> str:
    """Open the phone's share screen for a storage file inside an app (default WhatsApp; e.g. 'org.telegram.messenger',
    '' = choose). Then use screen_read + tap to pick the contact and press send."""
    return _txt(await call("share", 20, path=path, app=app, text=text))


app = mcp.streamable_http_app()
app.router.routes += [WebSocketRoute("/device", device_ws),
                      Route("/upload", upload, methods=["POST"]),
                      Route("/files/{name}", files)]

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=PORT, ws_ping_interval=20, ws_ping_timeout=20, ws_max_size=32 * 1024 * 1024)
