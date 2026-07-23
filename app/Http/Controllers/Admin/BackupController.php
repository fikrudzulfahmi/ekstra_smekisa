<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $directory = 'backups';
        $disk = Storage::disk('local');
        
        // Buat folder jika belum ada
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        $files = $disk->files($directory);
        
        $backups = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->formatSize($disk->size($file)),
                    'date' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                ];
            }
        }

        // Urutkan dari yang terbaru
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return inertia('Admin/Backup/Index', [
            'backups' => $backups,
        ]);
    }

    public function create()
    {
        try {
            // Jalankan command custom backup:sql
            Artisan::call('backup:sql');
            
            return back()->with('success', 'Backup berhasil dibuat. Silakan periksa daftar backup.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        
        $disk = Storage::disk('local');
        
        if ($disk->exists($request->path)) {
            return $disk->download($request->path);
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        
        $disk = Storage::disk('local');
        
        if ($disk->exists($request->path)) {
            $disk->delete($request->path);
            return back()->with('success', 'File backup berhasil dihapus.');
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimetypes:text/plain,application/sql,text/x-sql|max:51200' // maks 50MB
        ]);

        try {
            $file = $request->file('sql_file');
            $sql = file_get_contents($file->getRealPath());
            
            // Eksekusi SQL secara mentah
            // PERINGATAN: Pastikan ini hanya untuk lingkungan aman/terpercaya
            \DB::unprepared($sql);

            return back()->with('success', 'Database berhasil dipulihkan (restore) dari file SQL.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
