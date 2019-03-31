<?php
namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * @property array services
 * @property string context
 */
class DockerCompose extends OfflineModel {


    /**
     * DockerCompose constructor.
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function contextPath($path) {
        return $this->context . '/' . $path;
    }


    public function isValidService($service) {
        return isset($this->services[$service]);
    }

    public function save() {
        // save the docker-compose.yml file
        $this->writeToDockerComposeYaml();
        // check for missing folders
        $this->addMissingFoldersForServices();
        // handle dirty services, which were removed
        $this->deleteFoldersForServices();
        // write the env file changes
        $this->writeEnvFile();
    }

    public function writeEnvFile() {
        $envExamplePath = vendor_path('laradock/laradock/env-example');
        $envExample = file_get_contents($envExamplePath);
        preg_match_all('/\$\{(.*?)\}/m', json_encode($this->getAttributes()), $matches, PREG_SET_ORDER, 0);
        $environmentVariables = collect($matches)->map(function($res) {
            return $res[1];
        })->unique()->sort()->flip()->toArray();
        $dotEnv = collect(explode("\n", $envExample))->map(function($line) use ($environmentVariables) {
           if (Str::contains($line, '=')) {
               $key = substr($line, 0, strpos($line, '='));

               if (!isset($environmentVariables[$key])) {
                   return '';
               }

               $value = substr($line, strpos($line, '=') + 1);
               if ($key === 'APP_CODE_PATH_HOST') {
                   $value = './';
               }
               if (Str::contains($key, 'PUID')) {
                   $value = getmyuid();
               }
               if (Str::contains($key, 'PGID')) {
                   $value = getmygid();
               }
               return $key . '=' . $value;
           }
           return '';
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
