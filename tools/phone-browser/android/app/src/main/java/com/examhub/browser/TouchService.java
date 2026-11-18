package com.examhub.browser;

import android.accessibilityservice.AccessibilityService;
import android.accessibilityservice.GestureDescription;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.content.pm.ResolveInfo;
import android.graphics.Path;
import android.os.Build;
import android.os.Bundle;
import android.util.DisplayMetrics;
import android.view.WindowManager;
import android.view.accessibility.AccessibilityEvent;
import android.view.accessibility.AccessibilityNodeInfo;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.List;

/** Remote touch on the whole phone (any app). User turns it on once in Settings → Accessibility. */
public class TouchService extends AccessibilityService {
    static volatile TouchService inst;

    @Override protected void onServiceConnected() { inst = this; }
    @Override public void onAccessibilityEvent(AccessibilityEvent e) {}
    @Override public void onInterrupt() {}
    @Override public boolean onUnbind(Intent i) { inst = null; return super.onUnbind(i); }
    @Override public void onDestroy() { inst = null; super.onDestroy(); }

    private float[] px(double fx, double fy) {
        DisplayMetrics dm = new DisplayMetrics();
        ((WindowManager) getSystemService(WINDOW_SERVICE)).getDefaultDisplay().getRealMetrics(dm);
        return new float[]{(float) (Math.max(0, Math.min(1, fx)) * (dm.widthPixels - 1)),
                (float) (Math.max(0, Math.min(1, fy)) * (dm.heightPixels - 1))};
    }

    /** x,y are 0-1 fractions of the whole screen. */
    boolean tap(double x, double y, boolean longPress) {
        float[] p = px(x, y);
        Path path = new Path(); path.moveTo(p[0], p[1]);
        return dispatchGesture(new GestureDescription.Builder()
                .addStroke(new GestureDescription.StrokeDescription(path, 0, longPress ? 700 : 60)).build(), null, null);
    }

    boolean swipe(double x1, double y1, double x2, double y2, long ms) {
        float[] a = px(x1, y1), b = px(x2, y2);
        Path path = new Path(); path.moveTo(a[0], a[1]); path.lineTo(b[0], b[1]);
        return dispatchGesture(new GestureDescription.Builder()
                .addStroke(new GestureDescription.StrokeDescription(path, 0, Math.max(50, Math.min(ms, 3000)))).build(), null, null);
    }

    boolean key(String k) {
        int a;
        switch (k) {
            case "back": a = GLOBAL_ACTION_BACK; break;
            case "home": a = GLOBAL_ACTION_HOME; break;
            case "recents": a = GLOBAL_ACTION_RECENTS; break;
            case "notifications": a = GLOBAL_ACTION_NOTIFICATIONS; break;
            case "quick_settings": a = GLOBAL_ACTION_QUICK_SETTINGS; break;
            case "power_menu": a = GLOBAL_ACTION_POWER_DIALOG; break;
            case "lock": if (Build.VERSION.SDK_INT >= 28) { a = GLOBAL_ACTION_LOCK_SCREEN; break; } return false;
            default: return false;
        }
        return performGlobalAction(a);
    }

    /** Types into the field that has focus right now (in any app). */
    boolean type(String text) {
        AccessibilityNodeInfo root = getRootInActiveWindow();
        if (root == null) return false;
        AccessibilityNodeInfo f = root.findFocus(AccessibilityNodeInfo.FOCUS_INPUT);
        if (f == null) return false;
        Bundle b = new Bundle();
        b.putCharSequence(AccessibilityNodeInfo.ACTION_ARGUMENT_SET_TEXT_CHARSEQUENCE, text);
        return f.performAction(AccessibilityNodeInfo.ACTION_SET_TEXT, b);
    }

    /** Name of the app on screen now. */
    String currentApp() {
        AccessibilityNodeInfo root = getRootInActiveWindow();
        if (root == null || root.getPackageName() == null) return "";
        String pkg = root.getPackageName().toString();
        try { return getPackageManager().getApplicationLabel(getPackageManager().getApplicationInfo(pkg, 0)) + " (" + pkg + ")"; }
        catch (Exception e) { return pkg; }
    }

    static JSONArray apps(PackageManager pm, String filter) throws Exception {
        Intent i = new Intent(Intent.ACTION_MAIN).addCategory(Intent.CATEGORY_LAUNCHER);
        List<ResolveInfo> l = pm.queryIntentActivities(i, 0);
        JSONArray out = new JSONArray();
        String f = filter == null ? "" : filter.toLowerCase();
        java.util.TreeMap<String, String> sorted = new java.util.TreeMap<>(String.CASE_INSENSITIVE_ORDER);
        for (ResolveInfo r : l) {
            String name = r.loadLabel(pm).toString(), pkg = r.activityInfo.packageName;
            if (!f.isEmpty() && !name.toLowerCase().contains(f) && !pkg.toLowerCase().contains(f)) continue;
            sorted.put(name + "\u0000" + pkg, pkg);
        }
        for (String k : sorted.keySet()) {
            String[] p = k.split("\u0000");
            out.put(new JSONObject().put("name", p[0]).put("package", p[1]));
        }
        return out;
    }

    /** Opens an app by name or package. Uses this service's context so Android allows it from background. */
    static String openApp(android.content.Context ctx, String name) throws Exception {
        PackageManager pm = ctx.getPackageManager();
        JSONArray l = apps(pm, "");
        String n = name.toLowerCase().trim(), pkg = null;
        for (int i = 0; i < l.length() && pkg == null; i++)
            if (l.getJSONObject(i).getString("name").equalsIgnoreCase(n) || l.getJSONObject(i).getString("package").equals(name)) pkg = l.getJSONObject(i).getString("package");
        for (int i = 0; i < l.length() && pkg == null; i++)
            if (l.getJSONObject(i).getString("name").toLowerCase().contains(n)) pkg = l.getJSONObject(i).getString("package");
        if (pkg == null) throw new Exception("app nahi mila: " + name);
        Intent li = pm.getLaunchIntentForPackage(pkg);
        if (li == null) throw new Exception("app khul nahi sakta: " + pkg);
        li.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        ctx.startActivity(li);
        return pkg;
    }
}
