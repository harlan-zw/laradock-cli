<?php
namespace App\Tasks;


use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;

class ParseDockerComposeYaml
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
            $path = base_path() . '/' . self::DOCKER_COMPOSE_FILE;
        }
        $this->path = $path;
    }

    public function __invoke()
    {
        if (!invoke(new CheckDockerComposeYamlExists($this->path))) {
            Log::warning('Missing ' . $this->path . ' file.');
            return false;
        }

        $pr = Yaml::parseFile($this->path);
        Log::info('Parsed contents.');
        return $pr;
    }
}
