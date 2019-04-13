<?php

namespace App\Commands;

use Dotenv\Dotenv;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $laradockEnv = ('laradock-env');
        $this->line('Loading in laradock-env file at: ' . $laradockEnv);

        $laradockEnv = Dotenv::create(base_path(), 'laradock-env');
        $laradockAttributes = $laradockEnv->safeLoad();

        $process = new Process('docker-compose up -d', base_path());

        $process->setEnv($laradockAttributes)->run(function($line, $s) {
            $this->line('got output: ' . $line);
            dd($s);
        });
//        exec('source ' . $laradockEnv);
//        exec('source ' . $laradockEnv . '; docker-compose up -d');
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
