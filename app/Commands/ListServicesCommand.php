<?php

namespace App\Commands;

use App\Tasks\ParseDockerComposeYaml;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ListServicesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'services';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List the services you are able to enable';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parsed = invoke(new ParseDockerComposeYaml(vendor_path('laradock/laradock/docker-compose.yml')));
        $this->table(['Service'], collect(array_keys($parsed['services']))->map(function($service) {
            return [$service];
        }));
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
