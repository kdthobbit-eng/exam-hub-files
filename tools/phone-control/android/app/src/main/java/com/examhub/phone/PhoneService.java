package com.examhub.phone;

import android.accessibilityservice.AccessibilityService;
import android.accessibilityservice.GestureDescription;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.content.pm.ResolveInfo;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.graphics.Path;
import android.graphics.Rect;
import android.net.Uri;
import android.os.BatteryManager;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.provider.ContactsContract;
import android.util.Base64;
import android.util.DisplayMetrics;
import android.view.Display;
import android.view.WindowManager;
import android.view.accessibility.AccessibilityEvent;
import android.view.accessibility.AccessibilityNodeInfo;
import android.view.accessibility.AccessibilityWindowInfo;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.net.URLEncoder;
import java.text.SimpleDateFormat;
import java.util.ArrayDeque;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.Date;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Set;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.atomic.AtomicReference;

import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;
import okhttp3.WebSocket;
import okhttp3.WebSocketListener;

/** The heart of Exam Hub Phone: keeps the WebSocket to the VPS and carries out Claude's commands. */
public class PhoneService extends AccessibilityService {
    static volatile PhoneService instance;
    static volatile String status = "Band hai";

    private final Handler main = new Handler(Looper.getMainLooper());
    private final ExecutorService cmds = Executors.newSingleThreadExecutor();
    private final ExecutorService bg = Executors.newFixedThreadPool(2);
    private final OkHttpClient http = new OkHttpClient.Builder()
            .pingInterval(25, TimeUnit.SECONDS).readTimeout(0, TimeUnit.SECONDS).build();
    private WebSocket ws;
    private int backoff = 3;
    private boolean stopped = false;
    private volatile List<AccessibilityNodeInfo> lastNodes = new ArrayList<>();

    static SharedPreferences prefs(Context c) { return c.getSharedPreferences("cfg", MODE_PRIVATE); }

    // ------------------------------------------------------------ lifecycle
    @Override protected void onServiceConnected() {
        instance = this; stopped = false; connect();
    }

    @Override public boolean onUnbind(Intent i) {
        stopped = true; instance = null; status = "Accessibility band hai";
        if (ws != null) ws.close(1000, "bye");
        return super.onUnbind(i);
    }

    @Override public void onAccessibilityEvent(AccessibilityEvent e) {}
    @Override public void onInterrupt() {}

    static void reconnect() {
        PhoneService s = instance;
        if (s != null) { s.backoff = 3; if (s.ws != null) s.ws.cancel(); s.connect(); }
    }

    private String wsUrl() {
        String u = prefs(this).getString("url", "").trim();
        if (u.isEmpty()) return "";
        if (u.startsWith("https://")) u = "wss://" + u.substring(8);
        else if (u.startsWith("http://")) u = "ws://" + u.substring(7);
        if (u.endsWith("/mcp")) u = u.substring(0, u.length() - 4) + "/device";
        if (!u.endsWith("/device")) u = u.replaceAll("/+$", "") + "/device";
        return u;
    }

    private String baseHttp() {
        String u = wsUrl();
        u = u.substring(0, u.length() - "/device".length());
        return u.startsWith("wss://") ? "https://" + u.substring(6) : "http://" + u.substring(5);
    }

    private synchronized void connect() {
        String u = wsUrl();
        if (u.isEmpty()) { status = "Link daalo"; return; }
        status = "Jud raha hai…";
        ws = http.newWebSocket(new Request.Builder().url(u).build(), new WebSocketListener() {
            @Override public void onOpen(WebSocket w, Response r) {
                backoff = 3; status = "Juda hua ✓";
                try { w.send(hello().toString()); } catch (Exception ignored) {}
            }
            @Override public void onMessage(WebSocket w, String text) {
                cmds.submit(() -> handle(w, text));
            }
            @Override public void onFailure(WebSocket w, Throwable t, Response r) { retry(w, "Error: " + t.getMessage()); }
            @Override public void onClosed(WebSocket w, int code, String reason) { retry(w, "Band hua"); }
        });
    }

    private void retry(WebSocket w, String why) {
        if (w != ws || stopped) return;
        status = why + " — " + backoff + "s me dobara";
        main.postDelayed(() -> { if (w == ws && !stopped) connect(); }, backoff * 1000L);
        backoff = Math.min(backoff * 2, 60);
    }

    private JSONObject hello() throws Exception {
        JSONObject o = new JSONObject();
        o.put("type", "hello");
        o.put("model", Build.MANUFACTURER + " " + Build.MODEL);
        o.put("android", Build.VERSION.RELEASE + " (SDK " + Build.VERSION.SDK_INT + ")");
        o.put("app_version", BuildConfig.VERSION_NAME);
        o.put("permissions", perms());
        return o;
    }

    private JSONObject perms() throws Exception {
        JSONObject p = new JSONObject();
        p.put("storage", hasStorage());
        p.put("contacts", checkSelfPermission(android.Manifest.permission.READ_CONTACTS) == PackageManager.PERMISSION_GRANTED);
        p.put("notifications", NotifService.connected);
        p.put("screenshot", Build.VERSION.SDK_INT >= 30);
        return p;
    }

    boolean hasStorage() {
        if (Build.VERSION.SDK_INT >= 30) return Environment.isExternalStorageManager();
        return checkSelfPermission(android.Manifest.permission.READ_EXTERNAL_STORAGE) == PackageManager.PERMISSION_GRANTED;
    }

    // ------------------------------------------------------------ dispatcher
    private void handle(WebSocket w, String text) {
        JSONObject res = new JSONObject();
        try {
            JSONObject m = new JSONObject(text);
            res.put("id", m.optInt("id"));
            JSONObject a = m.optJSONObject("args");
            if (a == null) a = new JSONObject();
            Object out;
            switch (m.optString("cmd")) {
                case "status": out = cmdStatus(); break;
                case "read": out = cmdRead(a.optString("filter"), a.optInt("max_items", 150)); break;
                case "screenshot": out = cmdScreenshot(a.optBoolean("hq")); break;
                case "tap": out = cmdTap(a.optInt("index", -1), a.optDouble("x", -1), a.optDouble("y", -1), a.optBoolean("long")); break;
                case "type": out = cmdType(a.optString("text"), a.optInt("index", -1), a.optBoolean("clear", true), a.optBoolean("submit")); break;
                case "swipe": out = cmdSwipe(a); break;
                case "press": out = cmdPress(a.optString("key")); break;
                case "wait": out = cmdWait(a.optDouble("seconds", 2), a.optString("text")); break;
                case "apps": out = cmdApps(a.optString("filter")); break;
                case "open_app": out = cmdOpenApp(a.optString("name")); break;
                case "open_link": out = cmdOpenLink(a.optString("url"), a.optString("package")); break;
                case "whatsapp": out = cmdWhatsapp(a.optString("to"), a.optString("text"), a.optBoolean("send", true), a.optBoolean("business")); break;
                case "contacts": out = new JSONArray(contacts(a.optString("query"), 20)); break;
                case "notifs": out = cmdNotifs(a.optInt("limit", 20), a.optString("app")); break;
                case "toast": { String t = a.optString("text"); main.post(() -> Toast.makeText(this, t, Toast.LENGTH_LONG).show()); out = "ok"; break; }
                case "latest": out = cmdLatest(a); break;
                case "ls": out = cmdLs(a.optString("path")); break;
                case "upload": out = cmdUpload(a.optString("path"), a.optString("token")); break;
                case "download": out = cmdDownload(a.optString("url"), a.optString("filename")); break;
                case "share": out = cmdShare(a.optString("path"), a.optString("app"), a.optString("text")); break;
                default: throw new Exception("unknown command " + m.optString("cmd"));
            }
            res.put("ok", true); res.put("result", out);
        } catch (Throwable e) {
            try { res.put("ok", false); res.put("error", e.getClass().getSimpleName() + ": " + e.getMessage()); } catch (Exception ignored) {}
        }
        w.send(res.toString());
    }

    // ------------------------------------------------------------ helpers
    private static void sleep(long ms) { try { Thread.sleep(ms); } catch (InterruptedException ignored) {} }

    private DisplayMetrics screen() {
        DisplayMetrics dm = new DisplayMetrics();
        ((WindowManager) getSystemService(WINDOW_SERVICE)).getDefaultDisplay().getRealMetrics(dm);
        return dm;
    }

    private String appLabel(String pkg) {
        if (pkg == null) return "?";
        try { PackageManager pm = getPackageManager(); return pm.getApplicationLabel(pm.getApplicationInfo(pkg, 0)).toString(); }
        catch (Exception e) { return pkg; }
    }

    private String nowApp() {
        AccessibilityNodeInfo r = getRootInActiveWindow();
        if (r == null) return "?";
        String p = String.valueOf(r.getPackageName());
        return appLabel(p) + " (" + p + ")";
    }

    private List<AccessibilityNodeInfo> roots() {
        List<AccessibilityNodeInfo> out = new ArrayList<>();
        try {
            List<AccessibilityWindowInfo> ws = getWindows();
            // active app first, then system windows (status bar, dialogs), keyboard last
            for (AccessibilityWindowInfo w : ws)
                if (w.getType() == AccessibilityWindowInfo.TYPE_APPLICATION && w.getRoot() != null) out.add(w.getRoot());
            for (AccessibilityWindowInfo w : ws)
                if (w.getType() != AccessibilityWindowInfo.TYPE_APPLICATION && w.getType() != AccessibilityWindowInfo.TYPE_INPUT_METHOD && w.getRoot() != null) out.add(w.getRoot());
        } catch (Exception ignored) {}
        if (out.isEmpty()) { AccessibilityNodeInfo r = getRootInActiveWindow(); if (r != null) out.add(r); }
        return out;
    }

    private static String s(CharSequence c) { return c == null ? "" : c.toString().replace('\n', ' ').trim(); }

    private static String childText(AccessibilityNodeInfo n, int depth) {
        if (n == null || depth > 4) return "";
        StringBuilder b = new StringBuilder();
        for (int i = 0; i < n.getChildCount() && b.length() < 80; i++) {
            AccessibilityNodeInfo c = n.getChild(i);
            if (c == null) continue;
            String t = s(c.getText()); if (t.isEmpty()) t = s(c.getContentDescription());
            if (!t.isEmpty()) b.append(b.length() > 0 ? " | " : "").append(t);
            else { String d = childText(c, depth + 1); if (!d.isEmpty()) b.append(b.length() > 0 ? " | " : "").append(d); }
        }
        return b.length() > 80 ? b.substring(0, 80) + "…" : b.toString();
    }

    // ------------------------------------------------------------ screen
    private Object cmdStatus() throws Exception {
        JSONObject o = new JSONObject();
        o.put("on_screen", nowApp());
        BatteryManager bm = (BatteryManager) getSystemService(BATTERY_SERVICE);
        o.put("battery", bm.getIntProperty(BatteryManager.BATTERY_PROPERTY_CAPACITY) + "%");
        o.put("permissions", perms());
        return o;
    }

    private Object cmdRead(String filter, int max) {
        List<AccessibilityNodeInfo> nodes = new ArrayList<>();
        StringBuilder out = new StringBuilder("On screen: " + nowApp() + "\n");
        DisplayMetrics dm = screen();
        String f = filter == null ? "" : filter.toLowerCase();
        int shown = 0;
        for (AccessibilityNodeInfo root : roots()) {
            ArrayDeque<AccessibilityNodeInfo> st = new ArrayDeque<>();
            st.push(root);
            List<AccessibilityNodeInfo> order = new ArrayList<>();
            while (!st.isEmpty()) {           // pre-order DFS keeps reading order
                AccessibilityNodeInfo n = st.pop();
                order.add(n);
                for (int i = n.getChildCount() - 1; i >= 0; i--) { AccessibilityNodeInfo c = n.getChild(i); if (c != null) st.push(c); }
            }
            for (AccessibilityNodeInfo n : order) {
                if (!n.isVisibleToUser()) continue;
                Rect r = new Rect(); n.getBoundsInScreen(r);
                if (r.width() <= 1 || r.height() <= 1) continue;
                String text = s(n.getText()), desc = s(n.getContentDescription());
                String hint = Build.VERSION.SDK_INT >= 26 ? s(n.getHintText()) : "";
                boolean click = n.isClickable() || n.isLongClickable(), edit = n.isEditable(), scroll = n.isScrollable();
                if (text.isEmpty() && desc.isEmpty() && !click && !edit && !scroll) continue;
                String label = !text.isEmpty() ? text : desc;
                if (label.isEmpty() && click) label = childText(n, 0);
                if (label.isEmpty() && !edit && !scroll && !click) continue;
                String id = n.getViewIdResourceName() == null ? "" : n.getViewIdResourceName().replaceAll("^.*:id/", "");
                String all = (label + " " + desc + " " + hint + " " + id).toLowerCase();
                if (!f.isEmpty() && !all.contains(f)) continue;
                String cls = s(n.getClassName()).replaceAll("^.*\\.", "");
                StringBuilder line = new StringBuilder();
                line.append('[').append(nodes.size()).append("] ").append(cls);
                if (!label.isEmpty()) line.append(" \"").append(label.length() > 120 ? label.substring(0, 120) + "…" : label).append('"');
                if (!desc.isEmpty() && !desc.equals(label)) line.append(" desc=\"").append(desc).append('"');
                if (!hint.isEmpty()) line.append(" hint=\"").append(hint).append('"');
                if (!id.isEmpty()) line.append(" #").append(id);
                List<String> fl = new ArrayList<>();
                if (click) fl.add("tap"); if (edit) fl.add("type"); if (scroll) fl.add("scroll");
                if (n.isCheckable()) fl.add(n.isChecked() ? "ON" : "OFF");
                if (n.isFocused()) fl.add("focused");
                if (!fl.isEmpty()) line.append(" {").append(String.join(",", fl)).append('}');
                line.append(String.format(Locale.US, " @%.2f,%.2f", r.exactCenterX() / dm.widthPixels, r.exactCenterY() / dm.heightPixels));
                nodes.add(n);
                if (shown < max) { out.append(line).append('\n'); shown++; }
            }
        }
        lastNodes = nodes;
        if (nodes.size() > shown) out.append("… ").append(nodes.size() - shown).append(" aur items (filter use karo)\n");
        if (nodes.isEmpty()) out.append("(kuch nahi mila — screen lock ho sakti hai, ya screenshot lo)\n");
        return out.toString();
    }

    @android.annotation.TargetApi(30)
    private Object cmdScreenshot(boolean hq) throws Exception {
        if (Build.VERSION.SDK_INT < 30) throw new Exception("Screenshot ke liye Android 11+ chahiye — screen_read use karo");
        CountDownLatch l = new CountDownLatch(1);
        AtomicReference<Object> ref = new AtomicReference<>();
        takeScreenshot(Display.DEFAULT_DISPLAY, bg, new TakeScreenshotCallback() {
            @Override public void onSuccess(ScreenshotResult r) {
                try {
                    Bitmap hw = Bitmap.wrapHardwareBuffer(r.getHardwareBuffer(), r.getColorSpace());
                    Bitmap b = hw.copy(Bitmap.Config.ARGB_8888, false); hw.recycle(); r.getHardwareBuffer().close();
                    int w = hq ? 1080 : 720;
                    if (b.getWidth() > w) { Bitmap sc = Bitmap.createScaledBitmap(b, w, Math.round(b.getHeight() * (w / (float) b.getWidth())), true); b.recycle(); b = sc; }
                    ByteArrayOutputStream bo = new ByteArrayOutputStream();
                    b.compress(Bitmap.CompressFormat.JPEG, hq ? 85 : 60, bo);
                    JSONObject o = new JSONObject();
                    o.put("jpeg", Base64.encodeToString(bo.toByteArray(), Base64.NO_WRAP));
                    o.put("width", b.getWidth()); o.put("height", b.getHeight());
                    ref.set(o);
                } catch (Exception e) { ref.set(e); }
                l.countDown();
            }
            @Override public void onFailure(int code) {
                ref.set(new Exception(code == 3 ? "Bahut jaldi-jaldi screenshot — 1 sec ruk ke dobara" : "Screenshot fail, code " + code));
                l.countDown();
            }
        });
        if (!l.await(12, TimeUnit.SECONDS)) throw new Exception("Screenshot timeout");
        if (ref.get() instanceof Exception) throw (Exception) ref.get();
        return ref.get();
    }

    // ------------------------------------------------------------ gestures
    private boolean gesture(float x1, float y1, float x2, float y2, long ms) throws Exception {
        Path p = new Path(); p.moveTo(x1, y1);
        if (x1 != x2 || y1 != y2) p.lineTo(x2, y2);
        GestureDescription g = new GestureDescription.Builder()
                .addStroke(new GestureDescription.StrokeDescription(p, 0, Math.max(1, ms))).build();
        CountDownLatch l = new CountDownLatch(1);
        AtomicBoolean ok = new AtomicBoolean(false);
        main.post(() -> {
            boolean d = dispatchGesture(g, new GestureResultCallback() {
                @Override public void onCompleted(GestureDescription gd) { ok.set(true); l.countDown(); }
                @Override public void onCancelled(GestureDescription gd) { l.countDown(); }
            }, main);
            if (!d) l.countDown();
        });
        l.await(ms + 3000, TimeUnit.MILLISECONDS);
        return ok.get();
    }

    private AccessibilityNodeInfo node(int i) throws Exception {
        List<AccessibilityNodeInfo> n = lastNodes;
        if (i < 0 || i >= n.size()) throw new Exception("index " + i + " nahi mila — pehle screen_read karo");
        AccessibilityNodeInfo x = n.get(i);
        if (!x.refresh()) throw new Exception("screen badal gayi — dobara screen_read karo");
        return x;
    }

    private Object cmdTap(int index, double x, double y, boolean lng) throws Exception {
        String how;
        if (index >= 0) {
            AccessibilityNodeInfo n = node(index), c = n;
            int act = lng ? AccessibilityNodeInfo.ACTION_LONG_CLICK : AccessibilityNodeInfo.ACTION_CLICK;
            while (c != null && !(lng ? c.isLongClickable() : c.isClickable())) c = c.getParent();
            if (c != null && c.performAction(act)) how = "tapped [" + index + "]";
            else {
                Rect r = new Rect(); n.getBoundsInScreen(r);
                gesture(r.exactCenterX(), r.exactCenterY(), r.exactCenterX(), r.exactCenterY(), lng ? 800 : 60);
                how = "touched [" + index + "] at its centre";
            }
        } else if (x >= 0 && y >= 0) {
            DisplayMetrics dm = screen();
            float px = (float) (x <= 1 ? x * dm.widthPixels : x), py = (float) (y <= 1 ? y * dm.heightPixels : y);
            if (!gesture(px, py, px, py, lng ? 800 : 60)) throw new Exception("tap nahi hua");
            how = "tapped at " + x + "," + y;
        } else throw new Exception("index ya x,y do");
        sleep(700);
        return how + " → now: " + nowApp();
    }

    private Object cmdType(String text, int index, boolean clear, boolean submit) throws Exception {
        AccessibilityNodeInfo n = null;
        if (index >= 0) {
            n = node(index);
            if (!n.isEditable()) { n.performAction(AccessibilityNodeInfo.ACTION_CLICK); sleep(500); AccessibilityNodeInfo f = findFocus(AccessibilityNodeInfo.FOCUS_INPUT); if (f != null) n = f; }
        } else {
            n = findFocus(AccessibilityNodeInfo.FOCUS_INPUT);
            if (n == null) for (AccessibilityNodeInfo x : lastNodes) if (x.refresh() && x.isEditable()) { n = x; break; }
        }
        if (n == null) throw new Exception("koi input box nahi mila — pehle box par tap karo");
        String val = text;
        if (!clear) {
            boolean hint = Build.VERSION.SDK_INT >= 26 && n.isShowingHintText();
            if (!hint && n.getText() != null) val = n.getText() + text;
        }
        Bundle b = new Bundle();
        b.putCharSequence(AccessibilityNodeInfo.ACTION_ARGUMENT_SET_TEXT_CHARSEQUENCE, val);
        n.performAction(AccessibilityNodeInfo.ACTION_FOCUS);
        if (!n.performAction(AccessibilityNodeInfo.ACTION_SET_TEXT, b)) throw new Exception("is box me type nahi ho paya");
        String extra = "";
        if (submit) {
            sleep(300);
            if (Build.VERSION.SDK_INT >= 30) {
                n.refresh();
                if (!n.performAction(AccessibilityNodeInfo.AccessibilityAction.ACTION_IME_ENTER.getId())) extra = " (Enter nahi chala — send/search button tap karo)";
            } else extra = " (Enter ke liye button tap karo — Android 10 ya purana)";
            sleep(600);
        }
        return "typed" + extra;
    }

    private Object cmdSwipe(JSONObject a) throws Exception {
        DisplayMetrics dm = screen();
        double x1 = a.optDouble("x1", -1), y1 = a.optDouble("y1", -1), x2 = a.optDouble("x2", -1), y2 = a.optDouble("y2", -1);
        if (x1 < 0 || y1 < 0 || x2 < 0 || y2 < 0) {
            double h = Math.min(0.9, Math.max(0.1, a.optDouble("amount", 0.6))) / 2;
            x1 = x2 = 0.5; y1 = y2 = 0.5;
            switch (a.optString("direction", "up")) {
                case "down": y1 = 0.5 - h; y2 = 0.5 + h; break;
                case "left": x1 = 0.5 + h; x2 = 0.5 - h; break;
                case "right": x1 = 0.5 - h; x2 = 0.5 + h; break;
                default: y1 = 0.5 + h; y2 = 0.5 - h;
            }
        }
        boolean ok = gesture((float) (x1 * dm.widthPixels), (float) (y1 * dm.heightPixels),
                (float) (x2 * dm.widthPixels), (float) (y2 * dm.heightPixels), a.optInt("ms", 350));
        sleep(500);
        return ok ? "swiped" : "swipe cancel hua";
    }

    private Object cmdPress(String key) throws Exception {
        int act;
        switch (key) {
            case "back": act = GLOBAL_ACTION_BACK; break;
            case "home": act = GLOBAL_ACTION_HOME; break;
            case "recents": act = GLOBAL_ACTION_RECENTS; break;
            case "notifications": act = GLOBAL_ACTION_NOTIFICATIONS; break;
            case "quick_settings": act = GLOBAL_ACTION_QUICK_SETTINGS; break;
            case "power_menu": act = GLOBAL_ACTION_POWER_DIALOG; break;
            case "split_screen": act = GLOBAL_ACTION_TOGGLE_SPLIT_SCREEN; break;
            case "lock":
                if (Build.VERSION.SDK_INT < 28) throw new Exception("lock ke liye Android 9+");
                act = GLOBAL_ACTION_LOCK_SCREEN; break;
            default: throw new Exception("key: back/home/recents/notifications/quick_settings/lock/power_menu/split_screen");
        }
        if (!performGlobalAction(act)) throw new Exception(key + " nahi chala");
        sleep(600);
        return key + " ✓ → now: " + nowApp();
    }

    private String screenText() {
        StringBuilder b = new StringBuilder();
        for (AccessibilityNodeInfo root : roots()) {
            ArrayDeque<AccessibilityNodeInfo> st = new ArrayDeque<>(); st.push(root);
            while (!st.isEmpty()) {
                AccessibilityNodeInfo n = st.pop();
                b.append(s(n.getText())).append(' ').append(s(n.getContentDescription())).append(' ');
                for (int i = 0; i < n.getChildCount(); i++) { AccessibilityNodeInfo c = n.getChild(i); if (c != null) st.push(c); }
            }
        }
        return b.toString().toLowerCase();
    }

    private Object cmdWait(double sec, String text) {
        if (text == null || text.isEmpty()) { sleep((long) (Math.min(sec, 30) * 1000)); return "waited"; }
        long end = System.currentTimeMillis() + (long) (Math.min(sec <= 2 ? 20 : sec, 30) * 1000);
        String t = text.toLowerCase();
        while (System.currentTimeMillis() < end) {
            if (screenText().contains(t)) return "'" + text + "' aa gaya";
            sleep(700);
        }
        return "'" + text + "' nahi aaya (timeout)";
    }

    // ------------------------------------------------------------ apps
    private List<String[]> launchables() {
        PackageManager pm = getPackageManager();
        Intent i = new Intent(Intent.ACTION_MAIN).addCategory(Intent.CATEGORY_LAUNCHER);
        List<String[]> out = new ArrayList<>(); Set<String> seen = new HashSet<>();
        for (ResolveInfo r : pm.queryIntentActivities(i, 0)) {
            String p = r.activityInfo.packageName;
            if (seen.add(p)) out.add(new String[]{r.loadLabel(pm).toString(), p});
        }
        Collections.sort(out, (x, y) -> x[0].compareToIgnoreCase(y[0]));
        return out;
    }

    private Object cmdApps(String filter) {
        StringBuilder b = new StringBuilder(); String f = filter.toLowerCase(); int n = 0;
        for (String[] a : launchables())
            if (f.isEmpty() || (a[0] + " " + a[1]).toLowerCase().contains(f)) { b.append(a[0]).append(" — ").append(a[1]).append('\n'); n++; }
        return n + " apps\n" + b;
    }

    private Object cmdOpenApp(String name) throws Exception {
        String pkg = null, label = null, n = name.trim().toLowerCase();
        List<String[]> all = launchables();
        for (String[] a : all) if (a[1].equalsIgnoreCase(n) || a[0].equalsIgnoreCase(n)) { pkg = a[1]; label = a[0]; break; }
        if (pkg == null) for (String[] a : all) if (a[0].toLowerCase().contains(n)) { pkg = a[1]; label = a[0]; break; }
        if (pkg == null) throw new Exception("'" + name + "' naam ka app nahi mila — list_apps dekho");
        Intent i = getPackageManager().getLaunchIntentForPackage(pkg);
        i.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);
        startActivity(i);
        sleep(1800);
        return "opened " + label + " → now: " + nowApp();
    }

    private Object cmdOpenLink(String url, String pkg) throws Exception {
        Intent i = new Intent(Intent.ACTION_VIEW, Uri.parse(url)).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        if (!pkg.isEmpty()) i.setPackage(pkg);
        startActivity(i);
        sleep(1500);
        return "opened → now: " + nowApp();
    }

    private List<JSONObject> contacts(String q, int limit) throws Exception {
        if (checkSelfPermission(android.Manifest.permission.READ_CONTACTS) != PackageManager.PERMISSION_GRANTED)
            throw new Exception("Contacts permission nahi hai — app me 'Contacts' button dabao");
        List<JSONObject> out = new ArrayList<>(); Set<String> seen = new HashSet<>();
        String like = "%" + q + "%";
        try (Cursor c = getContentResolver().query(ContactsContract.CommonDataKinds.Phone.CONTENT_URI,
                new String[]{ContactsContract.CommonDataKinds.Phone.DISPLAY_NAME, ContactsContract.CommonDataKinds.Phone.NUMBER},
                ContactsContract.CommonDataKinds.Phone.DISPLAY_NAME + " LIKE ? OR " + ContactsContract.CommonDataKinds.Phone.NUMBER + " LIKE ?",
                new String[]{like, like}, ContactsContract.CommonDataKinds.Phone.DISPLAY_NAME + " ASC")) {
            while (c != null && c.moveToNext() && out.size() < limit) {
                String name = c.getString(0), num = c.getString(1);
                String key = name + num.replaceAll("[^0-9]", "");
                if (!seen.add(key)) continue;
                JSONObject o = new JSONObject(); o.put("name", name); o.put("number", num); out.add(o);
            }
        }
        return out;
    }

    private Object cmdWhatsapp(String to, String text, boolean send, boolean business) throws Exception {
        String digits = to.replaceAll("[^0-9]", ""), name = "";
        if (digits.length() < 10) {
            List<JSONObject> c = contacts(to.trim(), 5);
            if (c.isEmpty()) throw new Exception("'" + to + "' contacts me nahi mila");
            if (c.size() > 1 && !c.get(0).getString("name").equalsIgnoreCase(to.trim())) {
                boolean same = true;
                for (JSONObject o : c) if (!o.getString("name").equals(c.get(0).getString("name"))) same = false;
                if (!same) throw new Exception("Kai contacts mile, number ya pura naam do: " + new JSONArray(c));
            }
            name = c.get(0).getString("name");
            digits = c.get(0).getString("number").replaceAll("[^0-9]", "");
        }
        if (digits.length() == 11 && digits.startsWith("0")) digits = digits.substring(1);
        if (digits.length() == 10) digits = "91" + digits;
        String pkg = business ? "com.whatsapp.w4b" : "com.whatsapp";
        Intent i = new Intent(Intent.ACTION_VIEW, Uri.parse("https://api.whatsapp.com/send?phone=" + digits + "&text=" + URLEncoder.encode(text, "UTF-8")));
        i.setPackage(pkg).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
        try { startActivity(i); } catch (Exception e) { throw new Exception((business ? "WhatsApp Business" : "WhatsApp") + " install nahi hai"); }
        JSONObject r = new JSONObject(); r.put("to", "+" + digits); if (!name.isEmpty()) r.put("name", name);
        if (!send) { sleep(2000); r.put("sent", false); r.put("note", "chat khul gayi, message type hai — send nahi kiya"); return r; }
        long end = System.currentTimeMillis() + 12000;
        while (System.currentTimeMillis() < end) {
            sleep(700);
            AccessibilityNodeInfo root = getRootInActiveWindow();
            if (root == null || !pkg.contentEquals(String.valueOf(root.getPackageName()))) continue;
            List<AccessibilityNodeInfo> b = root.findAccessibilityNodeInfosByViewId(pkg + ":id/send");
            if (b.isEmpty()) b = root.findAccessibilityNodeInfosByViewId(pkg + ":id/send_container");
            if (!b.isEmpty()) {
                AccessibilityNodeInfo n = b.get(0);
                while (n != null && !n.isClickable()) n = n.getParent();
                if (n != null && n.performAction(AccessibilityNodeInfo.ACTION_CLICK)) {
                    sleep(800); r.put("sent", true); return r;
                }
            }
            String st = screenText();
            if (st.contains("isn't on whatsapp") || st.contains("not on whatsapp") || st.contains("व्हाट्सएप पर नहीं"))
                throw new Exception("+" + digits + " WhatsApp par nahi hai");
        }
        r.put("sent", false); r.put("note", "Send button nahi mila — screen_read karke dekho");
        return r;
    }

    private Object cmdNotifs(int limit, String app) throws Exception {
        if (!NotifService.connected) throw new Exception("Notification access ON nahi hai — app me 'Notifications' button dabao");
        return new JSONArray(NotifService.get(limit, app));
    }

    // ------------------------------------------------------------ storage
    private File root() { return Environment.getExternalStorageDirectory(); }

    private File resolve(String path) throws Exception {
        if (!hasStorage()) throw new Exception("Storage permission nahi hai — app me 'Storage' button dabao");
        File f = path.startsWith("/") ? new File(path) : new File(root(), path);
        if (!f.exists()) throw new Exception("nahi mila: " + path);
        return f;
    }

    private String rel(File f) {
        String r = root().getAbsolutePath(), p = f.getAbsolutePath();
        return p.startsWith(r + "/") ? p.substring(r.length() + 1) : p;
    }

    private static String size(long b) {
        if (b < 1024) return b + " B";
        if (b < 1024 * 1024) return (b / 1024) + " KB";
        return String.format(Locale.US, "%.1f MB", b / 1048576.0);
    }

    private static final SimpleDateFormat DF = new SimpleDateFormat("dd MMM yyyy HH:mm", Locale.US);

    private Object cmdLatest(JSONObject a) throws Exception {
        if (!hasStorage()) throw new Exception("Storage permission nahi hai — app me 'Storage' button dabao");
        String type = a.optString("type").toLowerCase().replace(".", ""), q = a.optString("query").toLowerCase(), folder = a.optString("folder");
        int limit = Math.max(1, Math.min(100, a.optInt("limit", 20))), days = a.optInt("days", 0);
        Set<String> ext = new HashSet<>();
        switch (type) {
            case "": case "any": case "all": break;
            case "image": case "photo": ext.addAll(Arrays.asList("jpg", "jpeg", "png", "webp", "heic", "gif")); break;
            case "video": ext.addAll(Arrays.asList("mp4", "mkv", "3gp", "webm", "mov")); break;
            case "audio": ext.addAll(Arrays.asList("mp3", "m4a", "aac", "opus", "ogg", "wav", "amr")); break;
            case "doc": case "document": ext.addAll(Arrays.asList("pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "odt", "csv")); break;
            default: ext.add(type);
        }
        List<File> starts = new ArrayList<>();
        if (folder.isEmpty()) starts.add(root());
        else {
            File f = new File(root(), folder);
            if (f.isDirectory()) starts.add(f);
            if (folder.equalsIgnoreCase("whatsapp")) {
                File m = new File(root(), "Android/media/com.whatsapp/WhatsApp"); if (m.isDirectory()) starts.add(m);
            }
            if (starts.isEmpty()) {
                File[] top = root().listFiles();
                if (top != null) for (File t : top) if (t.isDirectory() && t.getName().equalsIgnoreCase(folder)) starts.add(t);
            }
            if (starts.isEmpty()) throw new Exception("folder nahi mila: " + folder);
        }
        long since = days > 0 ? System.currentTimeMillis() - days * 86400000L : 0;
        String skip1 = new File(root(), "Android/data").getAbsolutePath(), skip2 = new File(root(), "Android/obb").getAbsolutePath();
        List<File> hits = new ArrayList<>();
        ArrayDeque<File> dq = new ArrayDeque<>(starts);
        int seen = 0;
        while (!dq.isEmpty() && seen < 80000) {
            File d = dq.poll(); File[] ls = d.listFiles();
            if (ls == null) continue;
            for (File f : ls) {
                if (f.getName().startsWith(".")) continue;
                if (f.isDirectory()) { String p = f.getAbsolutePath(); if (!p.equals(skip1) && !p.equals(skip2)) dq.add(f); continue; }
                seen++;
                String n = f.getName().toLowerCase();
                int dot = n.lastIndexOf('.');
                if (!ext.isEmpty() && (dot < 0 || !ext.contains(n.substring(dot + 1)))) continue;
                if (!q.isEmpty() && !n.contains(q)) continue;
                if (since > 0 && f.lastModified() < since) continue;
                hits.add(f);
            }
        }
        Collections.sort(hits, (x, y) -> Long.compare(y.lastModified(), x.lastModified()));
        JSONArray out = new JSONArray();
        for (int i = 0; i < Math.min(limit, hits.size()); i++) {
            File f = hits.get(i); JSONObject o = new JSONObject();
            o.put("name", f.getName()); o.put("path", rel(f)); o.put("size", size(f.length())); o.put("date", DF.format(new Date(f.lastModified())));
            out.put(o);
        }
        JSONObject r = new JSONObject(); r.put("found", hits.size()); r.put("files", out);
        return r;
    }

    private Object cmdLs(String path) throws Exception {
        File d = path.isEmpty() ? root() : resolve(path);
        File[] ls = d.listFiles();
        if (ls == null) throw new Exception("folder nahi khula");
        Arrays.sort(ls, (x, y) -> x.isDirectory() != y.isDirectory() ? (x.isDirectory() ? -1 : 1) : Long.compare(y.lastModified(), x.lastModified()));
        StringBuilder b = new StringBuilder(rel(d) + "/ (" + ls.length + ")\n");
        int n = 0;
        for (File f : ls) {
            if (n++ >= 300) { b.append("…\n"); break; }
            b.append(f.isDirectory() ? "📁 " + f.getName() + "/" : "   " + f.getName() + "  " + size(f.length()))
             .append("  ").append(DF.format(new Date(f.lastModified()))).append('\n');
        }
        return b.toString();
    }

    private Object cmdUpload(String path, String token) throws Exception {
        File f = resolve(path);
        if (f.isDirectory()) throw new Exception("ye folder hai, file do");
        if (f.length() > 150L * 1024 * 1024) throw new Exception("file 150 MB se badi hai");
        String url = baseHttp() + "/upload?t=" + URLEncoder.encode(token, "UTF-8") + "&name=" + URLEncoder.encode(f.getName(), "UTF-8");
        bg.submit(() -> {
            try {
                RequestBody body = RequestBody.create(f, MediaType.parse(ShareProvider.mimeOf(f.getName())));
                OkHttpClient c = http.newBuilder().writeTimeout(5, TimeUnit.MINUTES).readTimeout(60, TimeUnit.SECONDS).build();
                try (Response r = c.newCall(new Request.Builder().url(url).post(body).build()).execute()) { r.body(); }
            } catch (Exception ignored) {}
        });
        return "uploading " + size(f.length());
    }

    private Object cmdDownload(String url, String filename) throws Exception {
        if (!hasStorage()) throw new Exception("Storage permission nahi hai — app me 'Storage' button dabao");
        OkHttpClient c = http.newBuilder().readTimeout(2, TimeUnit.MINUTES).build();
        try (Response r = c.newCall(new Request.Builder().url(url).build()).execute()) {
            if (!r.isSuccessful()) throw new Exception("download fail: HTTP " + r.code());
            String name = filename;
            if (name.isEmpty()) {
                String cd = r.header("Content-Disposition", "");
                java.util.regex.Matcher m = java.util.regex.Pattern.compile("filename\\*?=(?:UTF-8'')?\"?([^\";]+)").matcher(cd);
                name = m.find() ? Uri.decode(m.group(1)) : Uri.parse(url).getLastPathSegment();
                if (name == null || name.isEmpty()) name = "download_" + System.currentTimeMillis();
            }
            name = name.replaceAll("[/\\\\:*?\"<>|]", "_");
            File dir = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS); dir.mkdirs();
            File out = new File(dir, name);
            try (InputStream in = r.body().byteStream(); FileOutputStream fo = new FileOutputStream(out)) {
                byte[] buf = new byte[65536]; int k;
                while ((k = in.read(buf)) > 0) fo.write(buf, 0, k);
            }
            android.media.MediaScannerConnection.scanFile(this, new String[]{out.getAbsolutePath()}, null, null);
            return "saved " + rel(out) + " (" + size(out.length()) + ")";
        }
    }

    private Object cmdShare(String path, String app, String text) throws Exception {
        File f = resolve(path);
        Uri u = ShareProvider.uriFor(f);
        Intent i = new Intent(Intent.ACTION_SEND).setType(ShareProvider.mimeOf(f.getName()))
                .putExtra(Intent.EXTRA_STREAM, u).addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
        if (!text.isEmpty()) i.putExtra(Intent.EXTRA_TEXT, text);
        Intent go;
        if (!app.isEmpty()) { i.setPackage(app); go = i; grantUriPermission(app, u, Intent.FLAG_GRANT_READ_URI_PERMISSION); }
        else go = Intent.createChooser(i, "Share");
        go.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        try { startActivity(go); } catch (Exception e) { throw new Exception(app + " app nahi khula: " + e.getMessage()); }
        sleep(2000);
        return "share screen khul gayi → now: " + nowApp() + ". Ab screen_read karke contact chuno aur send dabao.";
    }
}
