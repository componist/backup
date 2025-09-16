<?php

declare(strict_types=1);

namespace Componist\Backup;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Componist\Backup\Commands\DatabaseBackupCommand;

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
            //$schedule->command('componist:db-backup')->dailyAt('01:00');
            $schedule->command('componist:db-backup')->everyMinute();
        });

        $this->publishes([
            __DIR__.'/../config/componist_backup.php' => config_path('componist_backup.php'),
        ],'componist_backup');
    }
}