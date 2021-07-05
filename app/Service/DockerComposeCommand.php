<?php

namespace Laradock\Service;

use function Laradock\getLaradockCLIEnvPath;
use Laradock\Tasks\ParseDotEnvFile;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class DockerComposeCommand extends BaseCommand
{
    public $command;

    public $tty = false;

    /**
     * DockerComposeCommand constructor.
     * @param $command
     */
    public function __construct()
    {
        if (empty($this->signature)) {
            $this->signature = str_replace('docker-compose ', '', $this->command).' {cmd?* : The docker-compose arguments}';
        }
        if (empty($this->description)) {
            $this->description = 'Runs `'.$this->command.'` with the `.env.laradock` loaded in.';
        }
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Loading in .env.laradock file at: '.getLaradockCLIEnvPath('.env.laradock'));
        $laradockAttributes = \Laradock\invoke(new ParseDotEnvFile(getLaradockCLIEnvPath(), '.env.laradock'));
        $command = $this->command.' '.implode(' ', $this->input->getArgument('cmd'));
        $process = new Process($command, \Laradock\workingDirectory(), $laradockAttributes, null, 60000);

        $this->info($command);
        $process->setTty($this->tty);
        $process->run(function ($response, $output) {
            $this->output->write($output);
        });
    }
}
