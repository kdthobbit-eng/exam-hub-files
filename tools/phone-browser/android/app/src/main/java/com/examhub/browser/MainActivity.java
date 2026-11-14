package com.examhub.browser;

import android.Manifest;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.DownloadManager;
import android.content.*;
import android.graphics.*;
import android.net.Uri;
import android.os.*;
import android.util.Base64;
import android.view.*;
import android.view.inputmethod.EditorInfo;
import android.webkit.*;
import android.widget.*;

import org.json.*;

import java.io.ByteArrayOutputStream;
import java.util.ArrayList;
import java.util.concurrent.TimeUnit;

import okhttp3.*;

public class MainActivity extends Activity {
    private final ArrayList<WebView> tabs = new ArrayList<>();
    private int cur = -1;
    private FrameLayout holder;
    private EditText urlBar;
    private TextView status, tabBtn;
    private Switch claudeSw;
    private ProgressBar progress;
    private SharedPreferences prefs;
    private final Handler ui = new Handler(Looper.getMainLooper());

    private OkHttpClient http;
    private WebSocket ws;
    private boolean wantOn = false, connected = false;
    private ValueCallback<Uri[]> fileCb;

    // ---------------------------------------------------------------- UI
    @Override protected void onCreate(Bundle b) {
        super.onCreate(b);
        prefs = getSharedPreferences("cfg", MODE_PRIVATE);
        http = new OkHttpClient.Builder().pingInterval(25, TimeUnit.SECONDS).readTimeout(0, TimeUnit.MILLISECONDS).build();
        int dp = (int) getResources().getDisplayMetrics().density;

        LinearLayout root = new LinearLayout(this);
        root.setOrientation(LinearLayout.VERTICAL);
        LinearLayout bar = new LinearLayout(this);
        bar.setGravity(Gravity.CENTER_VERTICAL);
        bar.setPadding(4 * dp, 4 * dp, 4 * dp, 4 * dp);
        bar.setBackgroundColor(0xFFF1F5F9);

        status = new TextView(this);
        status.setText("●"); status.setTextSize(18); status.setPadding(6 * dp, 0, 4 * dp, 0);
        bar.addView(status);

        urlBar = new EditText(this);
        urlBar.setSingleLine(true); urlBar.setTextSize(14); urlBar.setHint("URL ya search");
        urlBar.setImeOptions(EditorInfo.IME_ACTION_GO);
        urlBar.setInputType(android.text.InputType.TYPE_TEXT_VARIATION_URI);
        urlBar.setOnEditorActionListener((v, a, e) -> { load(urlBar.getText().toString()); hideKb(); return true; });
        bar.addView(urlBar, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1));

        tabBtn = new TextView(this);
        tabBtn.setText("1"); tabBtn.setTextSize(15); tabBtn.setPadding(10 * dp, 4 * dp, 10 * dp, 4 * dp);
        tabBtn.setBackgroundColor(0xFFE2E8F0);
        tabBtn.setOnClickListener(v -> tabDialog());
        bar.addView(tabBtn);

        claudeSw = new Switch(this);
        claudeSw.setText("Claude"); claudeSw.setPadding(8 * dp, 0, 4 * dp, 0);
        claudeSw.setOnCheckedChangeListener((v, on) -> setClaude(on));
        bar.addView(claudeSw);

        TextView menu = new TextView(this);
        menu.setText("⋮"); menu.setTextSize(22); menu.setPadding(10 * dp, 0, 8 * dp, 0);
        menu.setOnClickListener(this::menu);
        bar.addView(menu);
        root.addView(bar);

        progress = new ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal);
        progress.setMax(100);
        root.addView(progress, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, 4 * dp));

        holder = new FrameLayout(this);
        root.addView(holder, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, 0, 1));
        setContentView(root);

        Uri data = getIntent().getData();
        newTab(data != null ? data.toString() : prefs.getString("home", "https://www.google.com"));
        if (Build.VERSION.SDK_INT >= 33) requestPermissions(new String[]{Manifest.permission.POST_NOTIFICATIONS}, 1);
        if (prefs.getBoolean("on", false) && !server().isEmpty()) claudeSw.setChecked(true);
        refreshStatus();
    }

    @Override protected void onNewIntent(Intent i) {
        super.onNewIntent(i);
        if (i.getData() != null) newTab(i.getData().toString());
    }

    private void hideKb() {
        ((android.view.inputmethod.InputMethodManager) getSystemService(INPUT_METHOD_SERVICE))
                .hideSoftInputFromWindow(urlBar.getWindowToken(), 0);
    }

    private void menu(View v) {
        PopupMenu p = new PopupMenu(this, v);
        p.getMenu().add("Connector link set karo");
        p.getMenu().add("Reload");
        p.getMenu().add("Aage (forward)");
        p.getMenu().add("Ye page home banao");
        p.setOnMenuItemClickListener(it -> {
            String t = it.getTitle().toString();
            if (t.startsWith("Connector")) serverDialog();
            else if (t.equals("Reload")) web().reload();
            else if (t.startsWith("Aage")) { if (web().canGoForward()) web().goForward(); }
            else { prefs.edit().putString("home", web().getUrl()).apply(); toast("Home set"); }
            return true;
        });
        p.show();
    }

    private void serverDialog() {
        EditText e = new EditText(this);
        e.setText(prefs.getString("server", ""));
        e.setHint("https://…sslip.io/SECRET/phone");
        new AlertDialog.Builder(this).setTitle("Phone connector link").setView(e)
                .setPositiveButton("Save", (d, w) -> {
                    prefs.edit().putString("server", e.getText().toString().trim()).apply();
                    if (wantOn) { disconnect(); connect(); }
                    else claudeSw.setChecked(true);
                }).setNegativeButton("Cancel", null).show();
    }

    private void tabDialog() {
        ArrayList<String> names = new ArrayList<>();
        for (int i = 0; i < tabs.size(); i++) {
            String t = tabs.get(i).getTitle();
            names.add((i == cur ? "▶ " : "") + (i + 1) + ". " + (t == null || t.isEmpty() ? tabs.get(i).getUrl() : t));
        }
        new AlertDialog.Builder(this).setTitle("Tabs (long press = band)")
                .setItems(names.toArray(new String[0]), (d, w) -> switchTab(w))
                .setPositiveButton("+ Naya tab", (d, w) -> newTab(prefs.getString("home", "https://www.google.com")))
                .setNeutralButton("Ye tab band karo", (d, w) -> closeTab(cur))
                .show();
    }

    private void toast(String s) { Toast.makeText(this, s, Toast.LENGTH_LONG).show(); }

    // ---------------------------------------------------------------- tabs / webview
    private WebView web() { return tabs.get(cur); }

    private interface Done { void run(); }
    private Done loadWaiter;

    private void newTab(String url) {
        WebView w = new WebView(this);
        WebSettings s = w.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setDatabaseEnabled(true);
        s.setLoadWithOverviewMode(true);
        s.setUseWideViewPort(true);
        s.setBuiltInZoomControls(true);
        s.setDisplayZoomControls(false);
        s.setSupportMultipleWindows(false);
        s.setJavaScriptCanOpenWindowsAutomatically(true);
        s.setMixedContentMode(WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE);
        s.setUserAgentString(s.getUserAgentString().replace("; wv", ""));
        CookieManager.getInstance().setAcceptThirdPartyCookies(w, true);
        w.setWebViewClient(new WebViewClient() {
            @Override public boolean shouldOverrideUrlLoading(WebView v, WebResourceRequest r) {
                String sc = r.getUrl().getScheme();
                if (sc != null && !sc.startsWith("http") && !sc.equals("about") && !sc.equals("data") && !sc.equals("blob")) {
                    try { startActivity(new Intent(Intent.ACTION_VIEW, r.getUrl())); } catch (Exception ignored) {}
                    return true;
                }
                return false;
            }
            @Override public void onPageStarted(WebView v, String u, Bitmap f) { if (v == web()) urlBar.setText(u); }
            @Override public void onPageFinished(WebView v, String u) {
                if (v == web()) urlBar.setText(u);
                if (v == web() && loadWaiter != null) { Done d = loadWaiter; loadWaiter = null; ui.postDelayed(d::run, 600); }
            }
        });
        w.setWebChromeClient(new WebChromeClient() {
            @Override public void onProgressChanged(WebView v, int p) {
                if (v == web()) { progress.setProgress(p); progress.setVisibility(p >= 100 ? View.INVISIBLE : View.VISIBLE); }
            }
            @Override public boolean onShowFileChooser(WebView v, ValueCallback<Uri[]> cb, FileChooserParams p) {
                if (fileCb != null) fileCb.onReceiveValue(null);
                fileCb = cb;
                try { startActivityForResult(p.createIntent(), 42); }
                catch (Exception e) { fileCb = null; return false; }
                toast("File chuniye (upload)");
                return true;
            }
        });
        w.setDownloadListener((u, ua, cd, mime, len) -> {
            try {
                DownloadManager.Request r = new DownloadManager.Request(Uri.parse(u));
                String name = URLUtil.guessFileName(u, cd, mime);
                r.addRequestHeader("Cookie", CookieManager.getInstance().getCookie(u));
                r.addRequestHeader("User-Agent", ua);
                r.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
                r.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, name);
                ((DownloadManager) getSystemService(DOWNLOAD_SERVICE)).enqueue(r);
                toast("Download: " + name);
            } catch (Exception e) { toast("Download nahi hua: " + e.getMessage()); }
        });
        tabs.add(w);
        switchTab(tabs.size() - 1);
        load(url);
    }

    private void switchTab(int i) {
        if (i < 0 || i >= tabs.size()) return;
        cur = i;
        holder.removeAllViews();
        holder.addView(web(), new FrameLayout.LayoutParams(-1, -1));
        urlBar.setText(web().getUrl());
        tabBtn.setText(String.valueOf(tabs.size()));
    }

    private void closeTab(int i) {
        if (i < 0 || i >= tabs.size()) return;
        WebView w = tabs.remove(i);
        holder.removeView(w);
        w.destroy();
        if (tabs.isEmpty()) { cur = -1; newTab(prefs.getString("home", "https://www.google.com")); return; }
        switchTab(Math.min(i, tabs.size() - 1));
    }

    private String fixUrl(String u) {
        u = u.trim();
        if (u.matches("^[a-zA-Z][a-zA-Z0-9+.-]*://.*")) return u;
        if (!u.contains(" ") && u.contains(".")) return "https://" + u;
        return "https://www.google.com/search?q=" + Uri.encode(u);
    }

    private void load(String u) { web().loadUrl(fixUrl(u)); }

    @Override protected void onActivityResult(int req, int res, Intent data) {
        if (req == 42 && fileCb != null) {
            fileCb.onReceiveValue(WebChromeClient.FileChooserParams.parseResult(res, data));
            fileCb = null;
        } else super.onActivityResult(req, res, data);
    }

    @Override public void onBackPressed() {
        if (cur >= 0 && web().canGoBack()) web().goBack();
        else moveTaskToBack(true);
    }

    // ---------------------------------------------------------------- connection
    private String server() { return prefs.getString("server", ""); }

    private String wsUrl() {
        String u = server();
        if (u.endsWith("/")) u = u.substring(0, u.length() - 1);
        if (u.endsWith("/mcp")) u = u.substring(0, u.length() - 4) + "/phone";
        if (!u.endsWith("/phone")) u = u + "/phone";
        return u.replaceFirst("^https://", "wss://").replaceFirst("^http://", "ws://");
    }

    private void setClaude(boolean on) {
        if (on && server().isEmpty()) { claudeSw.setChecked(false); serverDialog(); return; }
        wantOn = on;
        prefs.edit().putBoolean("on", on).apply();
        Intent svc = new Intent(this, KeepAliveService.class);
        if (on) {
            if (Build.VERSION.SDK_INT >= 26) startForegroundService(svc); else startService(svc);
            connect();
        } else { stopService(svc); disconnect(); }
        refreshStatus();
    }

    private void refreshStatus() {
        ui.post(() -> status.setTextColor(!wantOn ? 0xFF94A3B8 : connected ? 0xFF16A34A : 0xFFF59E0B));
    }

    private void disconnect() {
        if (ws != null) { ws.close(1000, "off"); ws = null; }
        connected = false; refreshStatus();
    }

    private void connect() {
        if (!wantOn || ws != null) return;
        Request r;
        try { r = new Request.Builder().url(wsUrl()).build(); }
        catch (Exception e) { toast("Link galat hai"); return; }
        ws = http.newWebSocket(r, new WebSocketListener() {
            @Override public void onOpen(WebSocket s, Response resp) {
                connected = true; refreshStatus();
                try {
                    s.send(new JSONObject().put("type", "hello").put("model", Build.MANUFACTURER + " " + Build.MODEL)
                            .put("android", Build.VERSION.RELEASE).toString());
                } catch (JSONException ignored) {}
                ui.post(() -> toast("Claude se jud gaya ✅"));
            }
            @Override public void onMessage(WebSocket s, String text) {
                try {
                    JSONObject m = new JSONObject(text);
                    if (m.has("cmd")) ui.post(() -> handle(s, m.optInt("id"), m.optString("cmd"), m.optJSONObject("args")));
                } catch (JSONException ignored) {}
            }
            @Override public void onClosed(WebSocket s, int c, String why) { lost(s); }
            @Override public void onFailure(WebSocket s, Throwable t, Response resp) { lost(s); }
        });
    }

    private void lost(WebSocket s) {
        if (ws != s) return;
        ws = null; connected = false; refreshStatus();
        if (wantOn) ui.postDelayed(this::connect, 5000);
    }

    // ---------------------------------------------------------------- commands
    private void reply(WebSocket s, int id, Object result) {
        try { s.send(new JSONObject().put("id", id).put("ok", true).put("result", result == null ? JSONObject.NULL : result).toString()); }
        catch (JSONException ignored) {}
    }

    private void fail(WebSocket s, int id, String err) {
        try { s.send(new JSONObject().put("id", id).put("ok", false).put("error", err).toString()); }
        catch (JSONException ignored) {}
    }

    private interface JsCb { void got(Object v); }

    /** Runs JS, parses its JSON result into JSONObject/JSONArray/String/Number/Boolean/NULL. */
    private void js(String code, JsCb cb) {
        web().evaluateJavascript(code, v -> {
            Object o = JSONObject.NULL;
            try { if (v != null && !v.equals("null")) o = new JSONTokener(v).nextValue(); } catch (JSONException e) { o = v; }
            cb.got(o);
        });
    }

    private static String q(String s) { return JSONObject.quote(s == null ? "" : s); }

    private JSONObject pageInfo() {
        JSONObject o = new JSONObject();
        try { o.put("tab", cur + 1).put("tabs", tabs.size()).put("url", web().getUrl()).put("title", web().getTitle()); }
        catch (JSONException ignored) {}
        return o;
    }

    private static final String EL_SEL = "a,button,input,select,textarea,[role=button],[role=link],[role=tab],[role=checkbox],[role=radio],[onclick],summary,label[for],[contenteditable=true]";

    private void handle(WebSocket s, int id, String cmd, JSONObject a) {
        if (a == null) a = new JSONObject();
        final JSONObject A = a;
        try {
            switch (cmd) {
                case "open": {
                    if (A.optBoolean("new_tab")) newTab(A.optString("url"));
                    else load(A.optString("url"));
                    waitLoad(() -> reply(s, id, pageInfo()));
                    break;
                }
                case "nav": {
                    String act = A.optString("action");
                    if (act.equals("back")) { if (!web().canGoBack()) { fail(s, id, "peeche koi page nahi"); return; } web().goBack(); }
                    else if (act.equals("forward")) { if (!web().canGoForward()) { fail(s, id, "aage koi page nahi"); return; } web().goForward(); }
                    else web().reload();
                    waitLoad(() -> reply(s, id, pageInfo()));
                    break;
                }
                case "tabs": {
                    String act = A.optString("action", "list");
                    int t = A.optInt("tab", -1) - 1;
                    if (act.equals("switch")) switchTab(t);
                    else if (act.equals("close")) closeTab(t);
                    else if (act.equals("new")) newTab(A.optString("url", "https://www.google.com"));
                    JSONArray arr = new JSONArray();
                    for (int i = 0; i < tabs.size(); i++)
                        arr.put(new JSONObject().put("tab", i + 1).put("current", i == cur)
                                .put("title", tabs.get(i).getTitle()).put("url", tabs.get(i).getUrl()));
                    reply(s, id, arr);
                    break;
                }
                case "text": {
                    int max = A.optInt("max_chars", 12000);
                    js("(function(){var t=(document.body?document.body.innerText:'');return {title:document.title,url:location.href,length:t.length,text:t.slice(0," + max + ")};})()",
                            v -> reply(s, id, v));
                    break;
                }
                case "elements": {
                    String code = "(function(f){f=(f||'').toLowerCase();var out=[],i=0;"
                            + "document.querySelectorAll('[data-chi]').forEach(function(e){e.removeAttribute('data-chi')});"
                            + "document.querySelectorAll(" + q(EL_SEL) + ").forEach(function(e){if(out.length>=250)return;"
                            + "var r=e.getBoundingClientRect(),st=getComputedStyle(e);"
                            + "if((r.width<2||r.height<2)&&!(e.type=='radio'||e.type=='checkbox'))return;if(st.visibility=='hidden'||st.display=='none')return;"
                            + "var lb='';if(e.labels&&e.labels[0])lb=e.labels[0].innerText;"
                            + "var t=(e.innerText||e.value||e.placeholder||e.getAttribute('aria-label')||lb||e.title||e.name||e.alt||'').trim().replace(/\\s+/g,' ').slice(0,80);"
                            + "if(e.tagName=='INPUT'&&lb&&t!=lb.trim())t=(lb.trim()+' | '+t).slice(0,100);"
                            + "if(f&&(t+' '+(e.name||'')+' '+(e.id||'')+' '+(e.placeholder||'')).toLowerCase().indexOf(f)<0)return;"
                            + "var d={i:i,tag:e.tagName.toLowerCase(),text:t};if(e.type&&e.tagName!='BUTTON')d.type=e.type;if(e.name)d.name=e.name;if(e.id)d.id=e.id;"
                            + "if(e.tagName=='SELECT'){d.value=e.options[e.selectedIndex]?e.options[e.selectedIndex].text:'';d.options=[].slice.call(e.options,0,40).map(function(o){return o.text.trim()})}"
                            + "else if(e.tagName=='INPUT'&&e.value&&e.type!='password'&&e.type!='submit'&&e.type!='button')d.value=e.value.slice(0,60);"
                            + "if(e.checked)d.checked=true;if(e.disabled)d.disabled=true;if(e.required)d.required=true;"
                            + "if(e.tagName=='A'&&e.href)d.href=e.href.slice(0,120);d.onScreen=r.bottom>0&&r.top<innerHeight;"
                            + "e.setAttribute('data-chi',i);out.push(d);i++;});return out;})(" + q(A.optString("filter")) + ")";
                    js(code, v -> reply(s, id, v));
                    break;
                }
                case "click": {
                    double x = A.optDouble("x", -1), y = A.optDouble("y", -1);
                    if (x >= 0 && y >= 0) {
                        tap((float) (x * web().getWidth()), (float) (y * web().getHeight()));
                        ui.postDelayed(() -> reply(s, id, pageInfo()), 900);
                        return;
                    }
                    String code = "(function(sel,n){var e=sel?document.querySelector(sel):document.querySelector('[data-chi=\"'+n+'\"]');"
                            + "if(!e)return {error:'element nahi mila — find_elements dobara chalao'};"
                            + "e.scrollIntoView({block:'center'});var r=e.getBoundingClientRect();"
                            + "return {x:(r.left+r.width/2)/innerWidth,y:(r.top+r.height/2)/innerHeight,text:(e.innerText||e.value||'').trim().slice(0,60)};})("
                            + q(A.optString("selector")) + "," + A.optInt("index", -1) + ")";
                    js(code, v -> {
                        if (!(v instanceof JSONObject) || ((JSONObject) v).has("error")) {
                            fail(s, id, v instanceof JSONObject ? ((JSONObject) v).optString("error") : "click fail"); return;
                        }
                        JSONObject o = (JSONObject) v;
                        float px = (float) (o.optDouble("x") * web().getWidth()), py = (float) (o.optDouble("y") * web().getHeight());
                        if (px < 0 || py < 0 || px > web().getWidth() || py > web().getHeight()) {
                            js("(function(sel,n){var e=sel?document.querySelector(sel):document.querySelector('[data-chi=\"'+n+'\"]');e.click();return 1})("
                                    + q(A.optString("selector")) + "," + A.optInt("index", -1) + ")", z -> {});
                        } else tap(px, py);
                        ui.postDelayed(() -> {
                            JSONObject info = pageInfo();
                            try { info.put("clicked", o.optString("text")); } catch (JSONException ignored) {}
                            reply(s, id, info);
                        }, 1200);
                    });
                    break;
                }
                case "type": {
                    String code = "(function(sel,n,txt,clear,submit){var e=sel?document.querySelector(sel):document.querySelector('[data-chi=\"'+n+'\"]');"
                            + "if(!e)return {error:'element nahi mila — find_elements dobara chalao'};e.scrollIntoView({block:'center'});e.focus();"
                            + "if(e.tagName=='SELECT'){var os=[].slice.call(e.options);var o=os.find(function(o){return o.text.trim()==txt||o.value==txt})"
                            + "||os.find(function(o){return o.text.toLowerCase().indexOf(txt.toLowerCase())>=0});if(!o)return {error:'option nahi mila'};"
                            + "e.value=o.value;e.dispatchEvent(new Event('input',{bubbles:true}));e.dispatchEvent(new Event('change',{bubbles:true}));return {selected:o.text.trim()}}"
                            + "if(e.isContentEditable){if(clear)e.innerText='';document.execCommand('insertText',false,txt);return {typed:true}}"
                            + "var proto=e.tagName=='TEXTAREA'?HTMLTextAreaElement.prototype:HTMLInputElement.prototype;"
                            + "var set=Object.getOwnPropertyDescriptor(proto,'value').set;set.call(e,(clear?'':e.value)+txt);"
                            + "e.dispatchEvent(new Event('input',{bubbles:true}));e.dispatchEvent(new Event('change',{bubbles:true}));e.dispatchEvent(new Event('blur',{bubbles:true}));"
                            + "if(submit){['keydown','keypress','keyup'].forEach(function(k){e.dispatchEvent(new KeyboardEvent(k,{key:'Enter',code:'Enter',keyCode:13,which:13,bubbles:true}))});"
                            + "if(e.form){if(e.form.requestSubmit)e.form.requestSubmit();else e.form.submit();}}"
                            + "return {value:e.type=='password'?'***':e.value}})("
                            + q(A.optString("selector")) + "," + A.optInt("index", -1) + "," + q(A.optString("text")) + ","
                            + A.optBoolean("clear", true) + "," + A.optBoolean("submit", false) + ")";
                    js(code, v -> {
                        if (v instanceof JSONObject && ((JSONObject) v).has("error")) fail(s, id, ((JSONObject) v).optString("error"));
                        else if (A.optBoolean("submit")) waitLoad(() -> reply(s, id, pageInfo()));
                        else reply(s, id, v);
                    });
                    break;
                }
                case "scroll": {
                    String dir = A.optString("direction", "down");
                    double amt = A.optDouble("amount", 0.8);
                    String code = dir.equals("top") ? "scrollTo(0,0)" : dir.equals("bottom") ? "scrollTo(0,document.body.scrollHeight)"
                            : "scrollBy(0," + (dir.equals("up") ? "-" : "") + "innerHeight*" + amt + ")";
                    js("(function(){" + code + ";return {scrollY:Math.round(scrollY),pageHeight:document.body.scrollHeight,viewHeight:innerHeight}})()",
                            v -> ui.postDelayed(() -> reply(s, id, v), 300));
                    break;
                }
                case "screenshot": {
                    WebView w = web();
                    int W = w.getWidth(), H = w.getHeight();
                    if (W <= 0 || H <= 0) { fail(s, id, "screen abhi dikh nahi rahi (app background mein?)"); return; }
                    Bitmap bm = Bitmap.createBitmap(W, H, Bitmap.Config.RGB_565);
                    w.draw(new Canvas(bm));
                    int maxW = A.optBoolean("hq") ? 1080 : 720;
                    if (W > maxW) { Bitmap sc = Bitmap.createScaledBitmap(bm, maxW, H * maxW / W, true); bm.recycle(); bm = sc; }
                    ByteArrayOutputStream out = new ByteArrayOutputStream();
                    bm.compress(Bitmap.CompressFormat.JPEG, A.optBoolean("hq") ? 85 : 60, out);
                    bm.recycle();
                    reply(s, id, new JSONObject().put("jpeg", Base64.encodeToString(out.toByteArray(), Base64.NO_WRAP)));
                    break;
                }
                case "js": {
                    js("(function(){try{var r=eval(" + q(A.optString("code")) + ");return JSON.parse(JSON.stringify(r===undefined?null:r))}catch(e){return {error:String(e)}}})()",
                            v -> reply(s, id, v));
                    break;
                }
                case "wait": {
                    String txt = A.optString("text");
                    double sec = Math.min(A.optDouble("seconds", 2), 30);
                    if (txt.isEmpty()) { ui.postDelayed(() -> reply(s, id, pageInfo()), (long) (sec * 1000)); return; }
                    long until = SystemClock.uptimeMillis() + 30000;
                    pollText(s, id, txt, until);
                    break;
                }
                case "toast": {
                    toast(A.optString("text"));
                    reply(s, id, "shown");
                    break;
                }
                default: fail(s, id, "unknown command " + cmd);
            }
        } catch (Exception e) { fail(s, id, e.toString()); }
    }

    private void pollText(WebSocket s, int id, String txt, long until) {
        js("(document.body&&document.body.innerText.indexOf(" + q(txt) + ")>=0)", v -> {
            if (Boolean.TRUE.equals(v)) reply(s, id, "mil gaya: " + txt);
            else if (SystemClock.uptimeMillis() > until) fail(s, id, "30s mein text nahi aaya: " + txt);
            else ui.postDelayed(() -> pollText(s, id, txt, until), 700);
        });
    }

    private void waitLoad(Done d) {
        final boolean[] fired = {false};
        Done once = () -> { if (!fired[0]) { fired[0] = true; d.run(); } };
        loadWaiter = once;
        ui.postDelayed(() -> { if (loadWaiter == once) loadWaiter = null; once.run(); }, 20000);
    }

    private void tap(float x, float y) {
        long t = SystemClock.uptimeMillis();
        MotionEvent down = MotionEvent.obtain(t, t, MotionEvent.ACTION_DOWN, x, y, 0);
        MotionEvent up = MotionEvent.obtain(t, t + 60, MotionEvent.ACTION_UP, x, y, 0);
        web().dispatchTouchEvent(down);
        web().dispatchTouchEvent(up);
        down.recycle(); up.recycle();
    }
}
