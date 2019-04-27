<?php

namespace Laradock\Service;

use Laradock\Tasks\ParseDotEnvFile;
use Symfony\Component\Process\Process;
use LaravelZero\Framework\Commands\Command;
use function Laradock\getLaradockCLIEnvPath;

class DockerComposeCommand extends Command
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
            $this->description = 'Runs `'.$this->command.'` with the `laradock-env` loaded in.';
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
        $this->line('Loading in laradock-env file at: '.getLaradockCLIEnvPath('.laradock-env'));
        $laradockAttributes = \Laradock\invoke(new ParseDotEnvFile(getLaradockCLIEnvPath(), '.laradock-env'));
        $command = $this->command.' '.implode(' ', $this->input->getArgument('cmd'));
        $process = new Process($command, \Laradock\workingDirectory(), $laradockAttributes, null, 60000);

        $this->info($command);
        $process->setTty($this->tty);
        $process->run(function ($response, $output) {
            $this->output->write($output);
        });
    }
}
