<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class ExecCommand extends DockerComposeCommand
{
    public $command = 'docker-compose exec';
}
