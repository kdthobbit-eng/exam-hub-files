"""Phone Browser MCP — lets Claude drive the Exam Hub Browser Android app.
Phone app connects by WebSocket to /phone; Claude connects to /mcp (both behind the Caddy secret path)."""
import asyncio, base64, itertools, json, os
from mcp.server.fastmcp import FastMCP, Image
from starlette.requests import Request
from starlette.responses import HTMLResponse, JSONResponse, Response
from starlette.routing import Route, WebSocketRoute
from starlette.websockets import WebSocket, WebSocketDisconnect

PORT = int(os.environ.get("PHONE_MCP_PORT", "8767"))
mcp = FastMCP("Phone Browser", host="127.0.0.1", port=PORT, stateless_http=True, json_response=True)
mcp.settings.transport_security = None  # Caddy in front, secret path protects

# Several phones can be connected at once; each is keyed by the name set in the app (⋮ → Phone ka naam).
PHONES: dict[str, dict] = {}   # name -> {"ws": WebSocket, "info": {...}}
ORDER: list[str] = []           # most recently connected last
PENDING: dict[int, asyncio.Future] = {}
IDS = itertools.count(1)

def _pick(device: str = ""):
    if not PHONES:
        raise RuntimeError("Koi phone connected nahi hai — phone par Exam Hub Browser kholo.")
    if device:
        d = device.lower().strip()
        for n in reversed(ORDER):
            if n.lower() == d: return n
        for n in reversed(ORDER):
            if d in n.lower() or d in json.dumps(PHONES[n]["info"]).lower(): return n
        raise RuntimeError(f"'{device}' naam ka phone nahi mila. Jude hue: {', '.join(ORDER)}")
    return ORDER[-1]

async def phone_ws(ws: WebSocket):
    await ws.accept()
    name = None
    try:
        while True:
            msg = json.loads(await ws.receive_text())
            if msg.get("type") == "hello":
                name = (msg.get("name") or msg.get("model") or "phone").strip()
                old = PHONES.get(name, {}).get("ws")
                PHONES[name] = {"ws": ws, "info": msg}
                if name in ORDER: ORDER.remove(name)
                ORDER.append(name)
                if old and old is not ws:
                    try: await old.close()
                    except Exception: pass
                continue
            if msg.get("type") == "ping":
                await ws.send_text('{"type":"pong"}'); continue
            fut = PENDING.pop(msg.get("id"), None)
            if fut and not fut.done(): fut.set_result(msg)
    except (WebSocketDisconnect, RuntimeError, json.JSONDecodeError):
        pass
    finally:
        if name and PHONES.get(name, {}).get("ws") is ws:
            PHONES.pop(name, None)
            if name in ORDER: ORDER.remove(name)

async def call(cmd: str, timeout: float = 40, device: str = "", **args):
    ws = PHONES[_pick(device)]["ws"]
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
    """Which phones are connected? For each: device info, screen cast / remote touch state, open tabs.
    Browser tools use the most recently connected phone unless a `device` name is given to the phone_* tools."""
    if not PHONES: return "Koi phone connected nahi hai."
    out = []
    for n in reversed(ORDER):
        d = {"name": n, "device": PHONES[n]["info"]}
        try: d["cast_touch"] = await call("cast_status", 10, device=n)
        except Exception as e: d["cast_touch"] = f"purana app (update karo): {e}"
        try: d["tabs"] = await call("tabs", 10, device=n)
        except Exception: pass
        out.append(d)
    return _txt(out)

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

# ---------------------------------------------------------------- whole phone (screen cast + remote touch)

@mcp.tool()
async def phone_screen(device: str = "", full_quality: bool = False) -> Image:
    """Screenshot of the WHOLE phone screen (any app, home screen too) via the app's screen cast.
    If cast is off, the phone shows the 'Start now' prompt and the user has to allow it once.
    device: phone name (see phone_status); empty = latest phone."""
    r = await call("screen", 30, device=device, max_width=1080 if full_quality else 720, quality=80 if full_quality else 60)
    return Image(data=base64.b64decode(r["jpeg"]), format="jpeg")

@mcp.tool()
async def phone_touch(x: float, y: float, long_press: bool = False, device: str = "") -> str:
    """Tap anywhere on the whole phone screen. x,y = 0-1 fractions of the phone_screen image (0,0 = top-left).
    Needs 'Exam Hub Remote Touch' ON in the phone's Accessibility settings."""
    return _txt(await call("ptap", 20, device=device, x=x, y=y, long=long_press))

@mcp.tool()
async def phone_swipe(direction: str = "", x1: float = 0.5, y1: float = 0.7, x2: float = 0.5, y2: float = 0.3,
                      ms: int = 350, device: str = "") -> str:
    """Swipe on the whole phone. direction up/down/left/right, or give x1,y1 → x2,y2 (0-1 fractions). up = scroll content down."""
    return _txt(await call("pswipe", 20, device=device, direction=direction, x1=x1, y1=y1, x2=x2, y2=y2, ms=ms))

@mcp.tool()
async def phone_button(key: str, device: str = "") -> str:
    """Press a phone button: back | home | recents | notifications | quick_settings | power_menu | lock."""
    return _txt(await call("pkey", 20, device=device, key=key))

@mcp.tool()
async def phone_type(text: str, device: str = "") -> str:
    """Type text into the box that is focused right now in any app (tap the box first with phone_touch)."""
    return _txt(await call("ptype", 20, device=device, text=text))

@mcp.tool()
async def phone_apps(filter: str = "", device: str = "") -> str:
    """List the apps installed on the phone (name + package). filter: optional words."""
    r = await call("apps", 20, device=device, filter=filter)
    return _txt({"count": len(r), "apps": r})

@mcp.tool()
async def phone_open_app(name: str, device: str = "") -> str:
    """Open an installed app by its name (e.g. 'WhatsApp', 'YouTube') or package."""
    return _txt(await call("open_app", 20, device=device, name=name))

# ---------------------------------------------------------------- live view page (for the user, AnyDesk-style)

VIEW_HTML = r"""<!doctype html><html><head><meta charset=utf-8>
<meta name=viewport content="width=device-width,initial-scale=1,user-scalable=no">
<title>Exam Hub Remote</title><style>
body{margin:0;background:#f8f5ef;font-family:system-ui,sans-serif;color:#3b2f20;display:flex;flex-direction:column;align-items:center}
header{width:100%;box-sizing:border-box;padding:8px 10px;display:flex;gap:6px;align-items:center;flex-wrap:wrap;background:#fff;border-bottom:1px solid #e7dcc8}
b{font-size:16px;margin-right:auto}select,button{font-size:14px;padding:6px 10px;border:1px solid #d6c7ad;border-radius:8px;background:#fff;color:#3b2f20}
#wrap{position:relative;margin:8px;touch-action:none}#scr{display:block;max-height:calc(100vh - 120px);max-width:96vw;border-radius:14px;box-shadow:0 2px 12px #0002;background:#ddd;user-select:none;-webkit-user-drag:none}
#msg{padding:6px;font-size:13px;color:#7a6142}.keys{display:flex;gap:8px;margin-bottom:10px}.keys button{min-width:70px}
</style></head><body>
<header><b>📱 Exam Hub Remote</b><select id=dev></select><input id=txt placeholder="Type karo…" style="font-size:14px;padding:6px;border:1px solid #d6c7ad;border-radius:8px;width:140px"><button onclick=typ()>Bhejo</button></header>
<div id=msg>Jud raha hai…</div>
<div id=wrap><img id=scr draggable=false></div>
<div class=keys><button onclick="key('back')">◀ Back</button><button onclick="key('home')">● Home</button><button onclick="key('recents')">▢ Apps</button></div>
<script>
const B=location.pathname.replace(/\/view\/?$/,''),img=document.getElementById('scr'),msg=document.getElementById('msg'),dev=document.getElementById('dev');
let busy=false,down=null;
function q(){return 'device='+encodeURIComponent(dev.value||'')}
async function devices(){try{const r=await (await fetch(B+'/api/devices')).json();const v=dev.value;dev.innerHTML=r.map(n=>`<option>${n}</option>`).join('')||'<option value="">koi phone nahi</option>';if(r.includes(v))dev.value=v;}catch(e){}}
async function frame(){if(busy)return;busy=true;try{const r=await fetch(B+'/api/frame?'+q()+'&t='+Date.now());
 if(r.ok){const b=await r.blob();const u=URL.createObjectURL(b);img.onload=()=>URL.revokeObjectURL(u);img.src=u;msg.textContent=(r.headers.get('x-app')||'Live')+' · touch/drag karke chalao';}
 else msg.textContent=await r.text();}catch(e){msg.textContent='Server se link toota, dobara jod raha hai…'}busy=false}
async function act(o){o.device=dev.value;await fetch(B+'/api/act',{method:'POST',headers:{'content-type':'application/json'},body:JSON.stringify(o)}).then(async r=>{if(!r.ok)msg.textContent=await r.text()});setTimeout(frame,350)}
function pos(e){const r=img.getBoundingClientRect();return[(e.clientX-r.left)/r.width,(e.clientY-r.top)/r.height]}
img.addEventListener('pointerdown',e=>{e.preventDefault();down={p:pos(e),t:Date.now()}});
img.addEventListener('pointerup',e=>{if(!down)return;const p=pos(e),dt=Date.now()-down.t,dx=p[0]-down.p[0],dy=p[1]-down.p[1];
 if(Math.hypot(dx,dy)<0.02)act({cmd:'ptap',x:p[0],y:p[1],long:dt>600});else act({cmd:'pswipe',x1:down.p[0],y1:down.p[1],x2:p[0],y2:p[1],ms:Math.min(Math.max(dt,120),1500)});down=null});
function key(k){act({cmd:'pkey',key:k})}
function typ(){const t=document.getElementById('txt');if(t.value){act({cmd:'ptype',text:t.value});t.value=''}}
devices();setInterval(devices,5000);setInterval(frame,400);frame();
</script></body></html>"""

async def view_page(request: Request):
    return HTMLResponse(VIEW_HTML)

async def api_devices(request: Request):
    return JSONResponse(list(reversed(ORDER)))

async def api_frame(request: Request):
    try:
        r = await call("screen", 15, device=request.query_params.get("device", ""), max_width=720, quality=55, ask=False)
    except Exception as e:
        msg = str(e)
        if "cast band" in msg.lower():
            msg = "Screen cast band hai — phone par Exam Hub Browser me 📺 dabao aur 'Start now' karo."
        return Response(msg, status_code=503, media_type="text/plain; charset=utf-8")
    return Response(base64.b64decode(r["jpeg"]), media_type="image/jpeg",
                    headers={"cache-control": "no-store", "x-app": (r.get("app") or "").encode("ascii", "ignore").decode()})

async def api_act(request: Request):
    a = await request.json()
    cmd = a.pop("cmd", "")
    if cmd not in ("ptap", "pswipe", "pkey", "ptype"):
        return Response("bad cmd", status_code=400)
    dev = a.pop("device", "")
    try:
        return JSONResponse({"ok": True, "result": await call(cmd, 20, device=dev, **a)})
    except Exception as e:
        return Response(str(e), status_code=503, media_type="text/plain; charset=utf-8")

app = mcp.streamable_http_app()
app.router.routes.append(WebSocketRoute("/phone", phone_ws))
app.router.routes.append(Route("/view", view_page))
app.router.routes.append(Route("/api/devices", api_devices))
app.router.routes.append(Route("/api/frame", api_frame))
app.router.routes.append(Route("/api/act", api_act, methods=["POST"]))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=PORT, ws_ping_interval=20, ws_ping_timeout=20)
