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
    protected $description = 'Backup database to SQL format and upload to Google Drive (with auto-cleanup on Drive only)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup to SQL format...');

        $date = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "Ekstrakurikuler-{$date}.sql";
        
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

        $dumpPath = config('database.connections.mysql.dump.dump_binary_path');
        if ($dumpPath) {
            $dumper->setDumpBinaryPath($dumpPath);
        }

        // Fix for Windows "Can't create TCP/IP socket (10106)" error via web server
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (!getenv('SystemRoot')) {
                putenv('SystemRoot=C:\Windows');
            }
            if (!isset($_ENV['SystemRoot'])) {
                $_ENV['SystemRoot'] = 'C:\Windows';
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

        // Upload to Google Drive
        $this->info('Uploading to Google Drive...');
        $googleDisk = Storage::disk('google');
        $googlePath = "Ekstrakurikuler/{$filename}";
        
        $fileStream = fopen($localPath, 'r');
        $googleDisk->put($googlePath, $fileStream);
        if (is_resource($fileStream)) {
            fclose($fileStream);
        }
        $this->info("Successfully uploaded to Google Drive: {$googlePath}");

        // Cleanup old backups on Google Drive (keep only the newest one)
        $this->info('Cleaning up old backups on Google Drive...');
        $allFiles = $googleDisk->files('Ekstrakurikuler');
        
        // Filter only .sql files
        $sqlFiles = array_filter($allFiles, function($file) {
            return str_ends_with($file, '.sql');
        });

        // Sort by timestamp (newest last if named correctly)
        sort($sqlFiles);

        // Keep the latest 1
        if (count($sqlFiles) > 1) {
            $filesToDelete = array_slice($sqlFiles, 0, count($sqlFiles) - 1);
            foreach ($filesToDelete as $fileToDelete) {
                $googleDisk->delete($fileToDelete);
                $this->info("Deleted old backup on Google Drive: {$fileToDelete}");
            }
        }
        
        // Delete all old .zip files that they have in Google Drive to clean up the existing .zip backups!
        $zipFiles = array_filter($allFiles, function($file) {
            return str_ends_with($file, '.zip');
        });
        foreach ($zipFiles as $zipFile) {
            $googleDisk->delete($zipFile);
            $this->info("Deleted old ZIP backup on Google Drive: {$zipFile}");
        }

        $this->info('Backup process completed successfully.');
    }
}
