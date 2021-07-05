<?php

namespace Laradock\Models;

use Illuminate\Support\Facades\File;
use function Laradock\getDockerComposePath;
use function Laradock\getLaradockCLIEnvPath;
use function Laradock\getLaradockEnvExamplePath;
use Laradock\Tasks\ParseDotEnvFile;
use Laradock\Transformers\EnvironmentConfigTransformer;
use Symfony\Component\Yaml\Yaml;

/**
 * @property array services
 */
class DockerCompose extends OfflineModel
{
    public $envAttributes = [];
    public $laradockAttributes = [];
    public $matchedLaradockEnvs = [];
    public $laradockExampleContents = '';

    /**
     * DockerCompose constructor.
     * @param $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        if (empty($attributes['networks'])) {
            $this->setAttribute('networks', []);
        }
        if (empty($attributes['volumes'])) {
            $this->setAttribute('volumes', []);
        }
        if (empty($attributes['services'])) {
            $this->setAttribute('services', []);
        }
        if (empty($attributes['path'])) {
            $this->setAttribute('path', config('laradock.compose_file'));
        }
    }

    public function isValidService($service)
    {
        return isset($this->services[$service]);
    }

    public function save()
    {
        $this->readCurrentEnvFile();
        // save the docker-compose.yml file
        $this->writeToDockerComposeYaml();
        // check for missing folders
        $this->addMissingFoldersForServices();
        // handle dirty services, which were removed
        $this->deleteFoldersForServices();
        // write the env file changes
        $this->writeEnvFile();
    }

    public function readCurrentEnvFile()
    {
        $this->envAttributes = \Laradock\invoke(new ParseDotEnvFile());
        $this->laradockAttributes = \Laradock\invoke(new ParseDotEnvFile(getLaradockCLIEnvPath(), '.env.laradock'));
        $this->laradockExampleContents = File::get(getLaradockEnvExamplePath());
        preg_match_all('/\$\{(.*?)\}/m', json_encode([
            'services' => $this->services,
            'networks' => $this->networks,
            'volumes' => $this->volumes,
        ]), $matches, PREG_SET_ORDER, 0);
        $this->matchedLaradockEnvs = collect($matches)->map(function ($res) {
            return $res[1];
        })->unique()->sort()->flip()->toArray();
    }

    public function writeEnvFile()
    {
        $dotEnv = collect(explode("\n", $this->laradockExampleContents))
            ->map(function ($line) {
                return \Laradock\invoke(new EnvironmentConfigTransformer($this), $line);
            })
            ->filter(function ($line) {
                return ! empty($line);
            })
            ->implode("\n");

        File::put(getLaradockCLIEnvPath('.env.laradock'), $dotEnv);
    }

    public function writeToDockerComposeYaml()
    {
        $attrs = ['version' => '3'];
        $attrs = array_merge($attrs, collect($this->getAttributes())
            ->only(['services', 'networks', 'volumes'])
            ->toArray());
        File::put(getDockerComposePath(), Yaml::dump($attrs, 6, 2, Yaml::DUMP_OBJECT_AS_MAP));
    }

    public function addMissingFoldersForServices()
    {
        if (! File::isDirectory(\Laradock\getServicesPath())) {
            File::makeDirectory(\Laradock\getServicesPath());
        }
        collect($this->services)->keys()->each(function ($key) {
            if (empty($key)) {
                return;
            }
            if (
                File::isDirectory(\Laradock\getLaradockServicePath($key))
            ) {
                File::copyDirectory(\Laradock\getLaradockServicePath($key), \Laradock\getServicesPath($key));
            }
        });
    }

    public function deleteFoldersForServices()
    {
        if (empty($this->original['services'])) {
            return;
        }
        collect($this->original['services'])->keys()->filter(function ($key) {
            return ! isset($this->services[$key]);
        })->each(function ($key) {
            File::deleteDirectory(\Laradock\getServicesPath($key));
        });
    }
}
