<?php

namespace Laradock;

use Illuminate\Support\Str;

function invoke($class, $arguments = null)
{
    return $class($arguments);
}

function workingDirectory($path = '')
{
    return \getcwd().'/'.$path;
}

function getDockerComposePath()
{
    return workingDirectory('docker-compose.yml');
}
function getLaradockCLIEnvPath($path = '')
{
    return workingDirectory($path);
}
function getDotEnvPath()
{
    return workingDirectory('.env');
}
function getServicesPath($service = '')
{
    if (! empty($service) && ! Str::startsWith($service, '/')) {
        $service = '/'.$service;
    }
    $context = str_replace('./', '', config('laradock.context'));

    return workingDirectory($context.$service);
}

function getLaradockEnvExamplePath()
{
    return config('laradock.laradock_path').'/env-example';
}

function getLaradockDockerComposePath()
{
    return config('laradock.laradock_path').'/docker-compose.yml';
}

function getLaradockServicePath($service = '')
{
    return config('laradock.laradock_path').$service;
}
