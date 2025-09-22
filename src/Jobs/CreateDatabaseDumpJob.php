<?php

namespace Componist\Backup\Jobs;

use Componist\Backup\Notifications\BackupStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use PDO;
use ZipArchive;

class CreateDatabaseDumpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $config = config('componist_backup');
        $backupPath = $config['path'];
        $maxBackups = $config['max_backups'];
        $recipient = $config['notify_email'];

        if (! File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFile = "{$backupPath}/backup_{$timestamp}.sql";
        $zipFile = "{$backupPath}/backup_{$timestamp}.zip";

        try {
            // Connecting with PDO
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $tables = [];
            $stmt = $pdo->query('SHOW TABLES');
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $dump = "-- Backup erstellt am {$timestamp}\n\n";

            foreach ($tables as $table) {
                // Log::info("Backup: Exportiere Tabelle {$table}");

                // CREATE TABLE Statement
                $createStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $dump .= "-- Struktur von Tabelle `$table`\n";
                $dump .= "DROP TABLE IF EXISTS `$table`;\n";
                $dump .= $createStmt['Create Table'].";\n\n";

                // Daten
                $rows = $pdo->query("SELECT * FROM `$table`");
                foreach ($rows as $row) {
                    $values = array_map([$pdo, 'quote'], array_values($row));
                    $dump .= "INSERT INTO `$table` VALUES (".implode(', ', $values).");\n";
                }
                $dump .= "\n\n";
            }

            // SQL-File write
            File::put($sqlFile, $dump);

            // create ZIP file
            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                $zip->addFile($sqlFile, basename($sqlFile));
                $zip->close();
                File::delete($sqlFile); // nur ZIP behalten
            } else {
                throw new \Exception('ZIP konnte nicht erstellt werden.');
            }

            // Log::info("Backup erfolgreich erstellt: {$zipFile}");

            // Old Backups delete
            $files = collect(File::files($backupPath))
                ->sortByDesc(fn ($file) => $file->getCTime());

            if ($files->count() > $maxBackups) {
                $filesToDelete = $files->slice($maxBackups);
                foreach ($filesToDelete as $file) {
                    File::delete($file->getPathname());
                    // Log::info("Backup: Altes Backup gelöscht: " . $file->getFilename());
                }
            }

            // Notification: Seccess
            Notification::route('mail', $recipient)->notify(new BackupStatusNotification(
                'success',
                "Das PDO-Datenbank-Backup wurde erfolgreich erstellt: {$zipFile}"
            ));
        } catch (\Throwable $e) {
            // Log::error("Backup fehlgeschlagen: " . $e->getMessage());

            // Notification: Fail
            Notification::route('mail', $recipient)->notify(new BackupStatusNotification(
                'error',
                'Das PDO-Datenbank-Backup ist fehlgeschlagen: '.$e->getMessage()
            ));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
