<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\DbDumper\Databases\MySql;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupDatabaseSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:sql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database to SQL format and upload to Google Drive using Service Account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup to SQL format...');

        $dbName = config('database.connections.mysql.database');
        $date = Carbon::now()->format('Ymd_His');
        $filename = "backup_{$dbName}_{$date}.sql";
        
        $localDisk = Storage::disk('local');
        if (!$localDisk->exists('backups')) {
            $localDisk->makeDirectory('backups');
        }

        // Get the absolute path to the local disk's backups folder
        $localPath = $localDisk->path("backups/{$filename}");

        // Configure dumper
        $dumper = MySql::create()
            ->setDbName(config('database.connections.mysql.database'))
            ->setUserName(config('database.connections.mysql.username'))
            ->setPassword(config('database.connections.mysql.password'))
            ->setHost(config('database.connections.mysql.host'))
            ->setPort(config('database.connections.mysql.port'));

        // Resolve binary path: config → env fallback → XAMPP Windows default
        $dumpPath = config('database.connections.mysql.dump.dump_binary_path')
            ?: env('DUMP_BINARY_PATH', '');

        // Jika masih kosong, coba deteksi otomatis lokasi XAMPP / common paths di Windows
        if (empty($dumpPath) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $candidates = [
                'C:\\xampp\\mysql\\bin',
                'C:\\xampp64\\mysql\\bin',
                'D:\\xampp\\mysql\\bin',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin',
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate . '\\mysqldump.exe')) {
                    $dumpPath = $candidate;
                    break;
                }
            }
        }

        if (!empty($dumpPath)) {
            // Pastikan tidak ada trailing slash/backslash
            $dumpPath = rtrim($dumpPath, '/\\');
            $dumper->setDumpBinaryPath($dumpPath);
        }

        // Fix for Windows "Can't create TCP/IP socket" error via web server
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (!getenv('SystemRoot')) {
                putenv('SystemRoot=C:\\Windows');
            }
            if (!isset($_ENV['SystemRoot'])) {
                $_ENV['SystemRoot'] = 'C:\\Windows';
            }
        }

        try {
            $dumper->dumpToFile($localPath);
            
            // Check if file size is 0 bytes, indicating a silent failure
            if (File::size($localPath) === 0) {
                File::delete($localPath);
                throw new \Exception("mysqldump created an empty file. This usually means a connection issue from the web server context.");
            }
            
            $this->info("Successfully dumped database to {$localPath}");
        } catch (\Exception $e) {
            if (File::exists($localPath)) {
                File::delete($localPath);
            }
            $this->error("Database dump failed: " . $e->getMessage());
            // Re-throw so controller catches it
            throw $e;
        }

        // ---------------------------------------------------------------------
        // UPLOAD TO GOOGLE DRIVE VIA WEBHOOK (APPS SCRIPT)
        // ---------------------------------------------------------------------
        $webhookUrl = env('GDRIVE_WEBHOOK_URL');
        $webhookSecret = env('GDRIVE_WEBHOOK_SECRET', 'smekisa_rahasia_123');

        if (!$webhookUrl) {
            $msg = 'Upload Google Drive dilewati: Variabel GDRIVE_WEBHOOK_URL belum diset di .env';
            $this->warn($msg);
            \Illuminate\Support\Facades\Log::warning($msg);
            
            $this->info('Backup process completed successfully (file tersimpan lokal saja).');
            return;
        }

        try {
            $this->info('Mulai mengirim file ke Google Drive (via Webhook)...');
            \Illuminate\Support\Facades\Log::info('Mulai upload backup ke Webhook Google Drive...');
            
            // Konversi file ke base64 agar aman dari bug penguraian multipart di server webhook
            $base64Content = base64_encode(file_get_contents($localPath));
            
            $response = \Illuminate\Support\Facades\Http::timeout(120)->post($webhookUrl, [
                'secret' => $webhookSecret,
                'filename' => $filename,
                'file_base64' => $base64Content
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === 'success') {
                    $msg = "Berhasil diunggah ke Google Drive dengan ID File: " . $result['fileId'];
                    $this->info($msg);
                    \Illuminate\Support\Facades\Log::info($msg);
                    
                    if (!empty($result['deleted'])) {
                        foreach ($result['deleted'] as $deletedFile) {
                            $msgDel = "Menghapus backup lama di Drive: {$deletedFile}";
                            $this->info($msgDel);
                            \Illuminate\Support\Facades\Log::info($msgDel);
                        }
                    } else {
                        $this->info("Tidak ada backup lama yang perlu dihapus di Drive.");
                    }
                } else {
                    $errorMsg = isset($result['message']) ? $result['message'] : 'Respon tidak dikenali dari Webhook.';
                    throw new \Exception("Webhook menolak permintaan: " . $errorMsg);
                }
            } else {
                throw new \Exception("HTTP Error " . $response->status() . ": " . $response->body());
            }

        } catch (\Exception $e) {
            $msg = "Upload Google Drive gagal: " . $e->getMessage();
            $this->warn($msg);
            \Illuminate\Support\Facades\Log::error($msg);
        }

        $this->info('Backup process completed successfully.');

    }
}
