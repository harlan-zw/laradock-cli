<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class RestartCommand extends DockerComposeCommand
{
    public $command = 'docker-compose restart';
}
