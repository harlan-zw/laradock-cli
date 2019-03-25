<?php
namespace App\Models;

use Illuminate\Support\Facades\File;
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

    public function save()
    {
        // save the docker-compose.yml file
        $this->writeToDockerComposeYaml();
        // check for missing folders
        $this->addMissingFoldersForServices();
        // handle dirty services, which were removed
        $this->deleteFoldersForServices();
    }

    public function writeToDockerComposeYaml() {
        $safeAttributes = collect($this->getAttributes())->except(['context', 'path'])->toArray();
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
     */
    public function setContext(string $context): void {
        $this->context = $context;
    }

}
