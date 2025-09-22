<?php

namespace Componist\Backup\Commands;

use Componist\Backup\Jobs\CreateDatabaseDumpJob;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'componist:db-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt ein MySQL Backup als ZIP und löscht alte Backups automatisch';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CreateDatabaseDumpJob::dispatch();
    }
}
