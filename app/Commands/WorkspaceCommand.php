<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class WorkspaceCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'workspace';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Mounts yourself to the workspace container as Laradock user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        passthru('docker-compose exec workspace bash');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
