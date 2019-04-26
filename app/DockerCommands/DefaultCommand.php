<?php

namespace Laradock\DockerCommands;

use Laradock\Service\DockerComposeCommand;

class DefaultCommand extends DockerComposeCommand
{
    public $command = 'docker-compose up -d && docker-compose exec --user=laradock workspace bash';
}
