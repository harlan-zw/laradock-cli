<?php
namespace App\Models;

use Illuminate\Support\Collection;
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

    public function services() : Collection {
        return collect($this->services)->mapWithKeys(function($data, $service) {
            // need to programmatically add network and storage as well
            $data = array_merge([
                'container_name' => 'laradock_' . $service,
                'networks' => [ $this->networks[$service] ?? false ],
                'volumes' => [ $this->volumes[$service] ?? false ]
            ], $data);
            return [$service => new Service($data)];
        });
    }

    public function isValidService($service) {
        return $this->services()->has($service);
    }

    public function save() {
        dd($this->services());
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
     * @return DockerCompose
     */
    public function setContext(string $context): DockerCompose {
        $this->context = $context;
        return $this;
    }

}
