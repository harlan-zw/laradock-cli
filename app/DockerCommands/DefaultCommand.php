<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;
use Laradock\Tasks\CheckDockerComposeYamlExists;

class DefaultCommand extends DockerComposeCommand
{
    public $tty = true;

    public $command = 'docker-compose up -d && docker-compose exec --user=laradock workspace bash';

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
                'Would you like to run `laradock setup` instead?',
                true
            )) {
                $this->call('setup');

                return;
            }
        }

        $this->call('status');

        parent::handle();
    }
}
