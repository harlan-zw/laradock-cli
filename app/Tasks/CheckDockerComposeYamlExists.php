<?php

namespace Laradock\Tasks;

use Illuminate\Support\Facades\File;

class CheckDockerComposeYamlExists
{
    const DOCKER_COMPOSE_FILE = 'docker-compose.yml';

    private $path;

    /**
     * ParseDockerComposeYaml constructor.
     * @param bool $path
     */
    public function __construct($path = false)
    {
        if (empty($path)) {
            $path = \Laradock\workingDirectory(self::DOCKER_COMPOSE_FILE);
        }
        $this->path = $path;
    }

    public function __invoke()
    {
        if (! File::exists($this->path)) {
            return false;
        }
        return true;
    }
}
