<?php
namespace App\Models;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * @property array services
 * @property string context
 */
class DockerCompose extends OfflineModel {

    public $envAttributes = [];
    public $laradockAttributes = [];

    /**
     * DockerCompose constructor.
     * @param $attributes
     */
    public function __construct($attributes = []) {
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


    public function contextPath($path) {
        return $this->context . '/' . $path;
    }

    public function isValidService($service) {
        return isset($this->services[$service]);
    }

    public function save() {
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

    public function readCurrentEnvFile() {
        $dotEnv = Dotenv::create(base_path());
        $this->envAttributes =$dotEnv->load();
        $laradockEnv = Dotenv::create(base_path(), 'laradock-env');
        $this->laradockAttributes = $laradockEnv->safeLoad();
    }

    public function writeEnvFile() {
        $envExamplePath = vendor_path('laradock/laradock/env-example');
        $envExample = file_get_contents($envExamplePath);
        preg_match_all('/\$\{(.*?)\}/m', json_encode($this->getAttributes()), $matches, PREG_SET_ORDER, 0);
        $environmentVariables = collect($matches)->map(function($res) {
            return $res[1];
        })->unique()->sort()->flip()->toArray();
        $dotEnv = collect(explode("\n", $envExample))
            ->map(function($line) use ($environmentVariables) {
                if (Str::startsWith($line, '### ')) {
                    $keys = collect($this->services)->keys()->map(function($s) {
                        return strtoupper($s);
                    })->toArray();
                    foreach($keys as $key) {
                        // we add the comments in to be nice
                        if (Str::contains($line, $key) ||
                            Str::contains($line, str_replace('-', '_', $key)) ||
                            // apache2
                            Str::contains($line, str_replace('2', '', $key)) ||
                            Str::contains($line, 'Paths') ||
                            Str::contains($line, 'Drivers')
                        ) {
                            return $line;
                        }
                    }
                }
                if (!Str::contains($line, '=')) {
                    return '';
                }
                $key = substr($line, 0, strpos($line, '='));

                if (!isset($environmentVariables[$key])) {
                    return '';
                }

                $value = substr($line, strpos($line, '=') + 1);
                if ($key === 'APP_CODE_PATH_HOST') {
                    $value = $attributes['APP_URL'] ?? './';
                }
                if (Str::contains($key, 'PUID')) {
                    $value = getmyuid();
                }
                if (Str::contains($key, 'PGID')) {
                    $value = getmygid();
                }
                // we shouldn't override the values
                if (isset($this->laradockAttributes[$key])) {
                    $value = $this->laradockAttributes[$key];
                } else if (isset($this->envAttributes[$key])) {
                    $value = $this->envAttributes[$key];
                }
                // if the value has a space we need to wrap it in double-quotes
                if (Str::contains($value, ' ') && !Str::startsWith($value, '"')) {
                    $value = '"' . $value . '"';
                }
                return $key . '=' . $value;
            })
            ->filter(function($line) {
                return !empty($line);
            })
            ->implode("\n");
        file_put_contents(base_path('laradock-env'), $dotEnv);
    }

    public function writeToDockerComposeYaml() {
        $safeAttributes = collect($this->getAttributes())
            ->except(['context', 'path'])
            ->toArray();
        $safeAttributes['version'] = '3';
        File::put($this->path, Yaml::dump($safeAttributes, 6, 2));
    }

    public function addMissingFoldersForServices() {
        collect($this->services)->keys()->each(function($key) {
            $path = $this->contextPath($key);
            if (!File::isDirectory($path)) {
                File::copyDirectory(vendor_path('laradock/laradock/' . $key), $path);
            }
        });
    }

    public function deleteFoldersForServices() {
        if (empty($this->original['services'])) {
            return;
        }
        collect($this->original['services'])->keys()->filter(function($key) {
            return !isset($this->services[$key]);
        })->each(function($key) {
            File::deleteDirectory($this->contextPath($key));
        });
    }

    /**
     * @param string $context
     * @return DockerCompose
     */
    public function setContext(string $context): DockerCompose {
        $this->context = $context;
        return $this;
    }

}
