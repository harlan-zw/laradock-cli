<?php

namespace Laradock\DockerCommands;

use Laradock\Tasks\ParseDotEnvFile;
use Laradock\Service\DockerComposeCommand;
use Laradock\Tasks\CheckDockerComposeYamlExists;

class DefaultCommand extends DockerComposeCommand
{
    public $tty = true;

    public $command = 'docker-compose up -d && docker-compose exec --user=laradock workspace zsh';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! \Laradock\invoke(new CheckDockerComposeYamlExists)) {
            $this->warn('It looks like you have not setup laradock.');
            if ($this->confirm(
                'Would you like to run `laradock install` instead?',
                true
            )) {
                $this->call('install');

                return;
            }
        }

        $this->call('status');

        $env = \Laradock\invoke(new ParseDotEnvFile());

        $this->title('Starting '.$env['APP_NAME'].' '.$env['APP_URL']);

        parent::handle();
    }
}
