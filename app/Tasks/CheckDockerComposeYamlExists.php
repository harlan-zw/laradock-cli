<?php

namespace Laradock\Tasks;

use Illuminate\Support\Facades\Log;
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
            $path = base_path().'/'.self::DOCKER_COMPOSE_FILE;
        }
        $this->path = $path;
    }

    public function __invoke()
    {
        Log::info('Attempting to load docker-compose file at '.$this->path);
        if (! File::exists($this->path)) {
            Log::info('Failed to find docker-compose file at: '.$this->path);

            return false;
        }
        Log::info('Found docker-compose file.');

        return true;
    }
}
