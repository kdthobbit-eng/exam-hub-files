package com.examhub.phone;

import android.app.Notification;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.service.notification.NotificationListenerService;
import android.service.notification.StatusBarNotification;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;

/** Keeps the last 100 notifications so Claude can read them. */
public class NotifService extends NotificationListenerService {
    static final LinkedList<JSONObject> RECENT = new LinkedList<>();
    static volatile boolean connected = false;

    @Override public void onListenerConnected() {
        connected = true;
        try {
            for (StatusBarNotification s : getActiveNotifications()) add(s);
        } catch (Exception ignored) {}
    }

    @Override public void onListenerDisconnected() { connected = false; }

    @Override public void onNotificationPosted(StatusBarNotification s) { add(s); }

    private void add(StatusBarNotification s) {
        try {
            if (s.getPackageName().equals(getPackageName())) return;
            Bundle e = s.getNotification().extras;
            CharSequence title = e.getCharSequence(Notification.EXTRA_TITLE);
            CharSequence text = e.getCharSequence(Notification.EXTRA_BIG_TEXT);
            if (text == null) text = e.getCharSequence(Notification.EXTRA_TEXT);
            if (title == null && text == null) return;
            String app = s.getPackageName();
            try {
                PackageManager pm = getPackageManager();
                app = pm.getApplicationLabel(pm.getApplicationInfo(s.getPackageName(), 0)).toString();
            } catch (Exception ignored) {}
            JSONObject o = new JSONObject();
            o.put("app", app); o.put("package", s.getPackageName());
            o.put("title", title == null ? "" : title.toString());
            o.put("text", text == null ? "" : text.toString());
            o.put("time", new java.text.SimpleDateFormat("dd MMM HH:mm", java.util.Locale.US).format(new java.util.Date(s.getPostTime())));
            o.put("key", s.getKey());
            synchronized (RECENT) {
                for (int i = 0; i < RECENT.size(); i++)   // same notification updated -> replace
                    if (RECENT.get(i).optString("key").equals(s.getKey()) && RECENT.get(i).optString("text").equals(o.optString("text"))) { RECENT.remove(i); break; }
                RECENT.addFirst(o);
                while (RECENT.size() > 100) RECENT.removeLast();
            }
        } catch (Exception ignored) {}
    }

    static List<JSONObject> get(int limit, String app) {
        List<JSONObject> out = new ArrayList<>();
        String f = app == null ? "" : app.toLowerCase();
        synchronized (RECENT) {
            for (JSONObject o : RECENT) {
                if (!f.isEmpty() && !o.optString("app").toLowerCase().contains(f) && !o.optString("package").toLowerCase().contains(f)) continue;
                JSONObject c = new JSONObject();
                try { for (String k : new String[]{"app", "title", "text", "time"}) c.put(k, o.opt(k)); } catch (Exception ignored) {}
                out.add(c);
                if (out.size() >= limit) break;
            }
        }
        return out;
    }
}
