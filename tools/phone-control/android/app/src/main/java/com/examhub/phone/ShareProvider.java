package com.examhub.phone;

import android.content.ContentProvider;
import android.content.ContentValues;
import android.database.Cursor;
import android.database.MatrixCursor;
import android.net.Uri;
import android.os.Environment;
import android.os.ParcelFileDescriptor;
import android.provider.OpenableColumns;
import android.webkit.MimeTypeMap;

import java.io.File;
import java.io.FileNotFoundException;

/** Serves storage files as content:// URIs so other apps (WhatsApp, Telegram) can receive them. */
public class ShareProvider extends ContentProvider {
    public static final String AUTH = "com.examhub.phone.files";

    public static Uri uriFor(File f) {
        String root = Environment.getExternalStorageDirectory().getAbsolutePath();
        String rel = f.getAbsolutePath().startsWith(root) ? f.getAbsolutePath().substring(root.length()) : f.getAbsolutePath();
        return new Uri.Builder().scheme("content").authority(AUTH).path(rel).build();
    }

    private File fileOf(Uri u) throws FileNotFoundException {
        File root = Environment.getExternalStorageDirectory();
        File f = new File(root, u.getPath());
        try {
            if (!f.getCanonicalPath().startsWith(root.getCanonicalPath())) throw new FileNotFoundException("outside storage");
        } catch (java.io.IOException e) { throw new FileNotFoundException(e.getMessage()); }
        return f;
    }

    public static String mimeOf(String name) {
        int i = name.lastIndexOf('.');
        String m = i < 0 ? null : MimeTypeMap.getSingleton().getMimeTypeFromExtension(name.substring(i + 1).toLowerCase());
        return m == null ? "application/octet-stream" : m;
    }

    @Override public boolean onCreate() { return true; }

    @Override public String getType(Uri uri) { return mimeOf(uri.getLastPathSegment() == null ? "" : uri.getLastPathSegment()); }

    @Override public ParcelFileDescriptor openFile(Uri uri, String mode) throws FileNotFoundException {
        return ParcelFileDescriptor.open(fileOf(uri), ParcelFileDescriptor.MODE_READ_ONLY);
    }

    @Override public Cursor query(Uri uri, String[] proj, String sel, String[] args, String sort) {
        try {
            File f = fileOf(uri);
            MatrixCursor c = new MatrixCursor(new String[]{OpenableColumns.DISPLAY_NAME, OpenableColumns.SIZE});
            c.addRow(new Object[]{f.getName(), f.length()});
            return c;
        } catch (FileNotFoundException e) { return null; }
    }

    @Override public Uri insert(Uri uri, ContentValues v) { return null; }
    @Override public int delete(Uri uri, String s, String[] a) { return 0; }
    @Override public int update(Uri uri, ContentValues v, String s, String[] a) { return 0; }
}
