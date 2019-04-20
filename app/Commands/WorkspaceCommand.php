<?php

namespace Laradock\Commands;

use Illuminate\Console\Scheduling\Schedule;
use function Laradock\getLaradockCLIEnvPath;
use Laradock\Tasks\ParseDotEnvFile;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

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
        $this->line('Loading in laradock-env file at: '.getLaradockCLIEnvPath('.laradock-env'));
        $laradockAttributes = \Laradock\invoke(new ParseDotEnvFile(getLaradockCLIEnvPath(), '.laradock-env'));
        $command = 'docker-compose exec --user=laradock workspace bash';
        $process = new Process($command, \Laradock\workingDirectory(), $laradockAttributes, null, 60000);

        $this->info($command);
        $process->setTty(true);
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
