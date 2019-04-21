<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class DownCommand extends DockerComposeCommand
{
    public $command = 'docker-compose down';
}
