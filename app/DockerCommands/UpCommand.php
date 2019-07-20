<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class UpCommand extends DockerComposeCommand
{
    public $command = 'docker-compose up -d';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'up {cmd?* : The docker-compose arguments}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Runs `docker-compose up -d` with the `.env.laradock` loaded in.';
}
