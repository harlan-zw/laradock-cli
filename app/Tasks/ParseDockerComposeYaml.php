<?php

namespace Laradock\Tasks;

use Symfony\Component\Yaml\Yaml;
use Laradock\Models\DockerCompose;

class ParseDockerComposeYaml
{
    public const DOCKER_COMPOSE_FILE = 'docker-compose.yml';

    private $path;

    /**
     * ParseDockerComposeYaml constructor.
     * @param bool|string $path
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
        if (! \Laradock\invoke(new CheckDockerComposeYamlExists($this->path))) {
            return new DockerCompose([
                'path' => $this->path,
            ]);
        }

        $content = Yaml::parseFile($this->path);

        if (empty($content)) {
            return new DockerCompose();
        }

        $compose = new DockerCompose($content);
        $compose->path = $this->path;

        return $compose;
    }
}
