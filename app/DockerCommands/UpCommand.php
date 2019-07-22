<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class UpCommand extends DockerComposeCommand
{
    public $command = 'docker-compose up';

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
    protected $description = 'Runs `docker-compose up` with the `.env.laradock` loaded in.';
}
