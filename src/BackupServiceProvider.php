<?php

declare(strict_types=1);

namespace Componist\Backup;

use Componist\Backup\Commands\DatabaseBackupCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(__DIR__.'../../config/componist_autobackup.php', 'name');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->commands([
            DatabaseBackupCommand::class,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('componist:db-backup')->dailyAt(config('componist_backup.backup_daily_at'));
            // $schedule->command('componist:db-backup')->everyMinute();
        });

        $this->publishes([
            __DIR__.'/../config/componist_backup.php' => config_path('componist_backup.php'),
        ], 'componist.backup');
    }
}