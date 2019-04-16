<?php

namespace Laradock\Commands;

use Dotenv\Dotenv;
use Symfony\Component\Process\Process;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class DownCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'down';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Runs `docker-compose down` with the `laradock-env` loaded in.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $laradockEnv = Dotenv::create(base_path(), 'laradock-env');
        $this->line('Loading in laradock-env file at: '.base_path());
        $laradockAttributes = $laradockEnv->safeLoad();

        $process = new Process('docker-compose down', base_path(), $laradockAttributes, null, 60000);

        $process->run(function ($response, $output) {
            $this->output->write($output);
        });
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
