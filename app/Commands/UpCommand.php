<?php

namespace Laradock\Commands;

use Dotenv\Dotenv;
use Symfony\Component\Process\Process;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class UpCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'up';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Runs `docker-compose up -d` with the `laradock-env` loaded in.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $laradockEnv = Dotenv::create(\Laradock\workingDirectory(), 'laradock-env');
        $this->line('Loading in laradock-env file at: '.\Laradock\workingDirectory());
        $laradockAttributes = $laradockEnv->safeLoad();

        $process = new Process('docker-compose up -d', \Laradock\workingDirectory(), $laradockAttributes, null, 60000);

        $this->info('We are starting docker, this may take a while if you haven\'t ran this command before.');
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
