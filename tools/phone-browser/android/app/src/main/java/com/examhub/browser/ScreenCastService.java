package com.examhub.browser;

import android.app.*;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.PixelFormat;
import android.hardware.display.DisplayManager;
import android.hardware.display.VirtualDisplay;
import android.media.Image;
import android.media.ImageReader;
import android.media.projection.MediaProjection;
import android.media.projection.MediaProjectionManager;
import android.os.*;
import android.util.DisplayMetrics;
import android.view.WindowManager;

import java.io.ByteArrayOutputStream;
import java.nio.ByteBuffer;

/** Whole-phone screen cast. User allows once ("Start now"); it keeps running until stopped. */
public class ScreenCastService extends Service {
    static volatile ScreenCastService inst;
    private MediaProjection proj;
    private VirtualDisplay vd;
    private ImageReader reader;
    private HandlerThread thread;
    private Bitmap latest;
    private long lastFrame = 0;
    private int W, H;

    static boolean on() { return inst != null && inst.proj != null; }

    @Override public IBinder onBind(Intent i) { return null; }

    @Override public int onStartCommand(Intent intent, int flags, int id) {
        NotificationManager nm = getSystemService(NotificationManager.class);
        if (Build.VERSION.SDK_INT >= 26)
            nm.createNotificationChannel(new NotificationChannel("cast", "Screen cast", NotificationManager.IMPORTANCE_LOW));
        PendingIntent pi = PendingIntent.getActivity(this, 1, new Intent(this, MainActivity.class), PendingIntent.FLAG_IMMUTABLE);
        Notification.Builder b = Build.VERSION.SDK_INT >= 26 ? new Notification.Builder(this, "cast") : new Notification.Builder(this);
        Notification n = b.setContentTitle("Screen cast ON").setContentText("Phone ki screen Claude / remote ko dikh rahi hai")
                .setSmallIcon(android.R.drawable.ic_menu_camera).setContentIntent(pi).setOngoing(true).build();
        if (Build.VERSION.SDK_INT >= 29) startForeground(8, n, android.content.pm.ServiceInfo.FOREGROUND_SERVICE_TYPE_MEDIA_PROJECTION);
        else startForeground(8, n);

        if (intent == null || proj != null) return START_NOT_STICKY;
        int code = intent.getIntExtra("code", 0);
        Intent data = intent.getParcelableExtra("data");
        if (data == null) { stopSelf(); return START_NOT_STICKY; }
        MediaProjectionManager mpm = (MediaProjectionManager) getSystemService(Context.MEDIA_PROJECTION_SERVICE);
        proj = mpm.getMediaProjection(code, data);
        if (proj == null) { stopSelf(); return START_NOT_STICKY; }
        inst = this;

        thread = new HandlerThread("cast"); thread.start();
        Handler h = new Handler(thread.getLooper());
        proj.registerCallback(new MediaProjection.Callback() {
            @Override public void onStop() { stopSelf(); }
        }, h);

        DisplayMetrics dm = new DisplayMetrics();
        ((WindowManager) getSystemService(WINDOW_SERVICE)).getDefaultDisplay().getRealMetrics(dm);
        W = Math.min(720, dm.widthPixels);
        H = dm.heightPixels * W / dm.widthPixels;
        reader = ImageReader.newInstance(W, H, PixelFormat.RGBA_8888, 2);
        reader.setOnImageAvailableListener(r -> {
            Image img = null;
            try {
                img = r.acquireLatestImage();
                if (img == null) return;
                long now = SystemClock.uptimeMillis();
                if (now - lastFrame < 150) return;
                lastFrame = now;
                Image.Plane p = img.getPlanes()[0];
                ByteBuffer buf = p.getBuffer();
                int rowW = p.getRowStride() / p.getPixelStride();
                Bitmap full = Bitmap.createBitmap(rowW, H, Bitmap.Config.ARGB_8888);
                full.copyPixelsFromBuffer(buf);
                Bitmap crop = rowW == W ? full : Bitmap.createBitmap(full, 0, 0, W, H);
                if (crop != full) full.recycle();
                synchronized (this) { if (latest != null) latest.recycle(); latest = crop; }
            } catch (Exception ignored) {
            } finally { if (img != null) img.close(); }
        }, h);
        vd = proj.createVirtualDisplay("examhub-cast", W, H, dm.densityDpi * W / dm.widthPixels,
                DisplayManager.VIRTUAL_DISPLAY_FLAG_AUTO_MIRROR, reader.getSurface(), null, h);
        return START_NOT_STICKY;
    }

    /** Latest screen frame as JPEG bytes, or null if nothing captured yet. */
    static byte[] jpeg(int maxW, int quality) {
        ScreenCastService s = inst;
        if (s == null) return null;
        synchronized (s) {
            if (s.latest == null || s.latest.isRecycled()) return null;
            Bitmap bm = s.latest;
            boolean scaled = false;
            if (maxW > 0 && bm.getWidth() > maxW) {
                bm = Bitmap.createScaledBitmap(bm, maxW, bm.getHeight() * maxW / bm.getWidth(), true);
                scaled = true;
            }
            ByteArrayOutputStream out = new ByteArrayOutputStream();
            bm.compress(Bitmap.CompressFormat.JPEG, quality, out);
            if (scaled) bm.recycle();
            return out.toByteArray();
        }
    }

    @Override public void onDestroy() {
        inst = null;
        try { if (vd != null) vd.release(); } catch (Exception ignored) {}
        try { if (proj != null) proj.stop(); } catch (Exception ignored) {}
        proj = null;
        if (reader != null) reader.close();
        if (thread != null) thread.quitSafely();
        synchronized (this) { if (latest != null) latest.recycle(); latest = null; }
        super.onDestroy();
    }
}
