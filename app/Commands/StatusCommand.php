<?php

namespace App\Commands;

use App\Tasks\ParseDockerComposeYaml;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Spatie\Emoji\Emoji;
use Symfony\Component\Yaml\Yaml;

class StatusCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'status';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Check the status of your Laradock configuration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->task('Parse docker-compose.yml', new ParseDockerComposeYaml, Emoji::rocket() . "\n");
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
