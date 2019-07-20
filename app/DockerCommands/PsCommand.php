<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class PsCommand extends DockerComposeCommand
{
    public $command = 'docker-compose ps';
}
