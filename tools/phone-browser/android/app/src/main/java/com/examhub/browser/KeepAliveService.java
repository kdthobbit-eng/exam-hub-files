package com.examhub.browser;

import android.app.*;
import android.content.Intent;
import android.os.*;

/** Keeps the app process alive (foreground notification + wake lock) while "Claude ON". */
public class KeepAliveService extends Service {
    private PowerManager.WakeLock lock;
    @Override public IBinder onBind(Intent i) { return null; }
    @Override public int onStartCommand(Intent intent, int flags, int id) {
        NotificationManager nm = getSystemService(NotificationManager.class);
        if (Build.VERSION.SDK_INT >= 26)
            nm.createNotificationChannel(new NotificationChannel("claude", "Claude connection", NotificationManager.IMPORTANCE_LOW));
        PendingIntent pi = PendingIntent.getActivity(this, 0, new Intent(this, MainActivity.class), PendingIntent.FLAG_IMMUTABLE);
        Notification.Builder b = Build.VERSION.SDK_INT >= 26 ? new Notification.Builder(this, "claude") : new Notification.Builder(this);
        Notification n = b.setContentTitle("Claude ON").setContentText("Claude is browser ko chala sakta hai")
                .setSmallIcon(android.R.drawable.ic_menu_compass).setContentIntent(pi).setOngoing(true).build();
        if (Build.VERSION.SDK_INT >= 29) startForeground(7, n, android.content.pm.ServiceInfo.FOREGROUND_SERVICE_TYPE_DATA_SYNC);
        else startForeground(7, n);
        if (lock == null) {
            lock = ((PowerManager) getSystemService(POWER_SERVICE)).newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "examhub:claude");
            lock.acquire();
        }
        return START_STICKY;
    }
    @Override public void onDestroy() { if (lock != null && lock.isHeld()) lock.release(); super.onDestroy(); }
}
