<?php

namespace App\Http\Controllers\Superadmin;

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

        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
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

        usort($backups, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        return inertia('Superadmin/Backup/Index', [
            'backups' => $backups,
        ]);
    }

    public function create()
    {
        try {
            Artisan::call('backup:sql');
            return back()->with('success', 'Backup berhasil dibuat.');
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
            'sql_file' => 'required|file|mimetypes:text/plain,application/sql,text/x-sql|max:51200',
        ]);

        try {
            $sql = file_get_contents($request->file('sql_file')->getRealPath());
            \DB::unprepared($sql);
            return back()->with('success', 'Database berhasil dipulihkan dari file SQL.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
