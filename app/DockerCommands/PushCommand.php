<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class PushCommand extends DockerComposeCommand
{
    public $command = 'docker-compose push';
}
