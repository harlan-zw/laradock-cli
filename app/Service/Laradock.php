<?php
namespace App\Service;

use App\Models\DockerCompose;
use App\Tasks\ParseDockerComposeYaml;
use phpDocumentor\Reflection\Types\Boolean;

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
     * @return DockerCompose|Boolean
     */
    public function getOurDockerCompose() {
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

    public function hasService($service) {
        return isset($this->ourDockerCompose['services'][$service]);
    }

    public function isValidService($service) {
        return \in_array($service, $this->services(), true);
    }

    public function setContext($context) {
        if (empty($context)) {
            $context =  config('laradock.context');
        }
        if (!empty($this->ourDockerCompose)) {
            $this->ourDockerCompose->setContext($context);
        }
    }

    public function addService($service) {
        if (!$this->laradockDockerCompose->isValidService($service)) {
            return false;
        }

        $serviceToAdd = $this->laradockDockerCompose->services[$service];
        // fix context
        if (isset($serviceToAdd['build']['context'])) {
            $serviceToAdd['build']['context'] = $this->ourDockerCompose->contextPath($service);
        } else if (!empty($serviceToAdd['build'])) {
            $serviceToAdd['build'] = $this->ourDockerCompose->contextPath($service);
        }
        $newServices = array_merge($this->ourDockerCompose->services, [ $service => $serviceToAdd ]);
        $this->ourDockerCompose->services = $newServices;
        $this->ourDockerCompose->networks = $this->laradockDockerCompose->networks;
        if (isset($this->laradockDockerCompose->volumes[$service])) {
            $newVolumes = array_merge(
                $this->ourDockerCompose->volumes,
                [ $service => $this->laradockDockerCompose->volumes[$service] ]
            );
            $this->ourDockerCompose->volumes = $newVolumes;
        }
        $this->ourDockerCompose->save();
        return true;
    }

    public function removeService($service) {
        if (!$this->isValidService($service)) {
            return false;
        }
        $services = $this->ourDockerCompose->services;
        // does not exist in our configuration
        if (isset($services[$service])) {
            unset($services[$service]);
            $this->ourDockerCompose->services = $services;
        }
        // remove the volume for the service we're removing
        $newVolumes =  $this->ourDockerCompose->volumes;
        if (isset($newVolumes[$service])) {
            unset($newVolumes[$service]);
            $this->ourDockerCompose->volumes = $newVolumes;
        }
        $this->ourDockerCompose->save();
    }

}
