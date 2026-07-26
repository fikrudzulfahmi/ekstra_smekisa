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
        // UPLOAD TO GOOGLE DRIVE (SERVICE ACCOUNT)
        // ---------------------------------------------------------------------
        $serviceAccountPath = env('GOOGLE_SERVICE_ACCOUNT_KEY_PATH');
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        $retentionCount = (int) env('BACKUP_RETENTION_COUNT', 7);

        if (!$serviceAccountPath || !$folderId) {
            $msg = 'Upload Google Drive dilewati: Variabel GOOGLE_SERVICE_ACCOUNT_KEY_PATH atau GOOGLE_DRIVE_FOLDER_ID belum diset di .env';
            $this->warn($msg);
            \Illuminate\Support\Facades\Log::warning($msg);
            
            $this->info('Backup process completed successfully (file tersimpan lokal saja).');
            return;
        }

        if (!file_exists($serviceAccountPath)) {
            $msg = "Upload Google Drive dilewati: File JSON Service Account tidak ditemukan di path {$serviceAccountPath}";
            $this->warn($msg);
            \Illuminate\Support\Facades\Log::warning($msg);
            
            $this->info('Backup process completed successfully (file tersimpan lokal saja).');
            return;
        }

        try {
            $this->info('Mengautentikasi ke Google Drive menggunakan Service Account...');
            \Illuminate\Support\Facades\Log::info('Mulai upload backup ke Google Drive...');
            
            $client = new \Google\Client();
            $client->setAuthConfig($serviceAccountPath);
            $client->addScope(\Google\Service\Drive::DRIVE);
            $service = new \Google\Service\Drive($client);

            $this->info('Mengunggah file ke Google Drive...');
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $filename,
                'parents' => [$folderId]
            ]);

            // Menggunakan stream untuk efisiensi memori jika file besar
            $content = file_get_contents($localPath);
            
            $uploadedFile = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/sql',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);
            
            $msg = "Berhasil diunggah ke Google Drive dengan ID File: {$uploadedFile->id}";
            $this->info($msg);
            \Illuminate\Support\Facades\Log::info($msg);

            // -----------------------------------------------------------------
            // RETENSI / AUTO-DELETE BACKUP LAMA
            // -----------------------------------------------------------------
            $this->info("Menjalankan logika retensi (menyimpan {$retentionCount} file terbaru)...");
            
            // Mencari file dengan awalan nama yang sama di folder target (tidak dibuang ke trash)
            $optParams = [
                'q' => "'{$folderId}' in parents and name contains 'backup_{$dbName}_' and mimeType = 'application/sql' and trashed = false",
                'orderBy' => 'createdTime desc',
                'fields' => 'files(id, name, createdTime)'
            ];
            
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();
            
            if (count($files) > $retentionCount) {
                $filesToDelete = array_slice($files, $retentionCount);
                foreach ($filesToDelete as $fileToDelete) {
                    $service->files->delete($fileToDelete->getId());
                    $msgDelete = "Menghapus backup lama di Drive: {$fileToDelete->getName()}";
                    $this->info($msgDelete);
                    \Illuminate\Support\Facades\Log::info($msgDelete);
                }
            } else {
                $this->info("Tidak ada backup lama yang perlu dihapus (total backup: " . count($files) . ").");
            }

        } catch (\Exception $e) {
            $msg = "Upload Google Drive gagal: " . $e->getMessage();
            $this->warn($msg);
            \Illuminate\Support\Facades\Log::error($msg);
        }

        $this->info('Backup process completed successfully.');

    }
}
