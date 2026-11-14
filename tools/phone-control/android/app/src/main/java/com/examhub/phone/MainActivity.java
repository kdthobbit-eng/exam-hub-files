package com.examhub.phone;

import android.app.Activity;
import android.content.ComponentName;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.os.PowerManager;
import android.provider.Settings;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.Response;

/** Setup screen: paste the VPS link, turn on permissions, check for updates. */
public class MainActivity extends Activity {
    private static final String RELEASE_API = "https://api.github.com/repos/kdthobbit-eng/exam-hub-files/releases/tags/phone-control";
    private final Handler h = new Handler(Looper.getMainLooper());
    private TextView status;
    private Button bA11y, bStorage, bContacts, bNotif, bBattery, bUpdate;
    private EditText link;

    private int dp(int v) { return Math.round(v * getResources().getDisplayMetrics().density); }

    private Button btn(LinearLayout p, String t, View.OnClickListener l) {
        Button b = new Button(this); b.setText(t); b.setAllCaps(false); b.setOnClickListener(l);
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(-1, -2); lp.topMargin = dp(6);
        p.addView(b, lp); return b;
    }

    private TextView label(LinearLayout p, String t, int sp) {
        TextView v = new TextView(this); v.setText(t); v.setTextSize(sp); v.setTextColor(Color.DKGRAY);
        v.setPadding(0, dp(10), 0, dp(2)); p.addView(v); return v;
    }

    @Override protected void onCreate(Bundle b) {
        super.onCreate(b);
        ScrollView sv = new ScrollView(this);
        LinearLayout p = new LinearLayout(this); p.setOrientation(LinearLayout.VERTICAL); p.setPadding(dp(18), dp(18), dp(18), dp(30));
        sv.addView(p);

        TextView title = label(p, "Exam Hub Phone", 24); title.setTextColor(Color.BLACK);
        label(p, "Version " + BuildConfig.VERSION_NAME + " — Claude se pura phone chalane ke liye", 13);
        status = label(p, "", 16); status.setTextColor(Color.parseColor("#1565C0"));

        label(p, "1. VPS se mila 'Phone app link' yahan daalo:", 14);
        link = new EditText(this); link.setHint("https://…/device"); link.setSingleLine(true);
        link.setText(PhoneService.prefs(this).getString("url", "")); p.addView(link);
        btn(p, "Link save karo", v -> {
            PhoneService.prefs(this).edit().putString("url", link.getText().toString().trim()).apply();
            PhoneService.reconnect();
            Toast.makeText(this, "Save ho gaya", Toast.LENGTH_SHORT).show();
        });

        label(p, "2. Permissions (sab ✓ hone chahiye):", 14);
        bA11y = btn(p, "", v -> startActivity(new Intent(Settings.ACTION_ACCESSIBILITY_SETTINGS)));
        bStorage = btn(p, "", v -> {
            if (Build.VERSION.SDK_INT >= 30) {
                try { startActivity(new Intent(Settings.ACTION_MANAGE_APP_ALL_FILES_ACCESS_PERMISSION, Uri.parse("package:" + getPackageName()))); }
                catch (Exception e) { startActivity(new Intent(Settings.ACTION_MANAGE_ALL_FILES_ACCESS_PERMISSION)); }
            } else requestPermissions(new String[]{android.Manifest.permission.READ_EXTERNAL_STORAGE, android.Manifest.permission.WRITE_EXTERNAL_STORAGE}, 1);
        });
        bContacts = btn(p, "", v -> requestPermissions(new String[]{android.Manifest.permission.READ_CONTACTS}, 2));
        bNotif = btn(p, "", v -> startActivity(new Intent("android.settings.ACTION_NOTIFICATION_LISTENER_SETTINGS")));
        bBattery = btn(p, "", v -> {
            try { startActivity(new Intent(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS, Uri.parse("package:" + getPackageName()))); }
            catch (Exception e) { startActivity(new Intent(Settings.ACTION_IGNORE_BATTERY_OPTIMIZATION_SETTINGS)); }
        });
        label(p, "Accessibility/Notification ka switch grey (band) dikhe to: neeche wala button → ⋮ (upar kone me) → \"Allow restricted settings\" → phir dobara ON karo.", 13);
        btn(p, "App info (restricted settings)", v -> startActivity(new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS, Uri.parse("package:" + getPackageName()))));

        label(p, "3. Update", 14);
        bUpdate = btn(p, "Update check karo", v -> checkUpdate(true));

        setContentView(sv);
        checkUpdate(false);
    }

    @Override protected void onResume() { super.onResume(); refresh(); }
    @Override protected void onPause() { super.onPause(); h.removeCallbacksAndMessages(null); }

    @Override public void onRequestPermissionsResult(int c, String[] p, int[] r) { refresh(); }

    private boolean a11yOn() {
        String s = Settings.Secure.getString(getContentResolver(), Settings.Secure.ENABLED_ACCESSIBILITY_SERVICES);
        return s != null && s.contains(new ComponentName(this, PhoneService.class).flattenToString());
    }

    private boolean notifOn() {
        String s = Settings.Secure.getString(getContentResolver(), "enabled_notification_listeners");
        return s != null && s.contains(getPackageName());
    }

    private void refresh() {
        boolean a = a11yOn();
        bA11y.setText((a ? "✓ " : "✗ ") + "Accessibility (zaroori — tap/type/screen)");
        boolean st = Build.VERSION.SDK_INT >= 30 ? Environment.isExternalStorageManager()
                : checkSelfPermission(android.Manifest.permission.READ_EXTERNAL_STORAGE) == PackageManager.PERMISSION_GRANTED;
        bStorage.setText((st ? "✓ " : "✗ ") + "Storage (files, PDF)");
        bContacts.setText((checkSelfPermission(android.Manifest.permission.READ_CONTACTS) == PackageManager.PERMISSION_GRANTED ? "✓ " : "✗ ") + "Contacts (naam se WhatsApp)");
        bNotif.setText((notifOn() ? "✓ " : "✗ ") + "Notifications padhna");
        PowerManager pm = (PowerManager) getSystemService(POWER_SERVICE);
        bBattery.setText((pm.isIgnoringBatteryOptimizations(getPackageName()) ? "✓ " : "✗ ") + "Battery: Unrestricted (connection na tute)");
        String s = !a ? "Accessibility ON karo" : TextUtils.isEmpty(PhoneService.prefs(this).getString("url", "")) ? "Link daalo" : PhoneService.status;
        status.setText("Status: " + s);
        h.postDelayed(this::refresh, 1500);
    }

    private void checkUpdate(boolean manual) {
        new Thread(() -> {
            try (Response r = new OkHttpClient().newCall(new Request.Builder().url(RELEASE_API).header("Accept", "application/vnd.github+json").build()).execute()) {
                JSONObject o = new JSONObject(r.body().string());
                Matcher m = Pattern.compile("v1\\.(\\d+)").matcher(o.optString("name"));
                int latest = m.find() ? Integer.parseInt(m.group(1)) : 0;
                String url = "";
                JSONArray as = o.optJSONArray("assets");
                if (as != null && as.length() > 0) url = as.getJSONObject(0).optString("browser_download_url");
                final String dl = url; final int lv = latest;
                h.post(() -> {
                    if (lv > BuildConfig.VERSION_CODE && !dl.isEmpty()) {
                        bUpdate.setText("⬆ Naya version v1." + lv + " — download karo");
                        bUpdate.setOnClickListener(v -> startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(dl))));
                    } else if (manual) Toast.makeText(this, "Aap latest version par ho", Toast.LENGTH_SHORT).show();
                });
            } catch (Exception e) {
                if (manual) h.post(() -> Toast.makeText(this, "Update check nahi hua: " + e.getMessage(), Toast.LENGTH_SHORT).show());
            }
        }).start();
    }
}
