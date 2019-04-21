<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class BuildCommand extends DockerComposeCommand
{
    public $command = 'docker-compose build';
}
