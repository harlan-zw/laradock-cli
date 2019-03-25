<?php
namespace App\Service;

use App\Models\DockerCompose;
use App\Tasks\ParseDockerComposeYaml;

class Laradock {

    /**
     * @var DockerCompose $laradockDockerCompose
     */
    private $laradockDockerCompose;

    /**
     * @var DockerCompose $ourDockerCompose
     */
    private $ourDockerCompose;

    /**
     * @return DockerCompose
     */
    public function getOurDockerCompose(): DockerCompose {
        return $this->ourDockerCompose;
    }

    /**
     * @return DockerCompose
     */
    public function getLaradockDockerCompose(): DockerCompose {
        return $this->laradockDockerCompose;
    }

    /**
     * Laradock constructor.
     */
    public function __construct($config) {
        $this->laradockDockerCompose = $this->parseDockerComposeYaml($config['laradock_path']);
        $this->ourDockerCompose = $this->parseDockerComposeYaml();
        $this->ourDockerCompose->setContext($config['context']);
    }

    private function parseDockerComposeYaml($path = '') {
        return invoke(
            new ParseDockerComposeYaml($path)
        );
    }

    public function services() {
        return collect($this->laradockDockerCompose['services'])->sortKeys()->keys()->toArray();
    }

    public function isValidService($service) {
        return \in_array($service, $this->services(), true);
    }

    public function setContext($context) {
        $this->ourDockerCompose->setContext($context);
    }

    public function addService($service) {
        if (!$this->isValidService($service)) {
            return false;
        }
        $services = $this->ourDockerCompose->services;
        $services[$service] = $this->laradockDockerCompose->services[$service];
        $services[$service]['build']['context'] = $this->ourDockerCompose->contextPath($service);
        $this->ourDockerCompose->services = $services;
        $this->ourDockerCompose->save();
    }

    public function removeService($service) {
        if (!$this->isValidService($service)) {
            return false;
        }
        $services = $this->ourDockerCompose->services;
        // does not exist in our configuration
        if (!isset($services[$service])) {
            return false;
        }
        unset($services[$service]);
        $this->ourDockerCompose->services = $services;
        $this->ourDockerCompose->save();
    }

}
