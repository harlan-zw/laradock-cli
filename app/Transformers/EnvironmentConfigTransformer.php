<?php

namespace Laradock\Transformers;

use Illuminate\Support\Str;
use Laradock\Models\DockerCompose;
use function Laradock\workingDirectory;

class EnvironmentConfigTransformer
{
    private $compose;

    /**
     * EnvironmentConfigTransformer constructor.
     */
    public function __construct(DockerCompose $compose)
    {
        $this->compose = $compose;
    }

    public function __invoke($line)
    {
        // Show comments for each services's section we're showing
        if (Str::startsWith($line, '### ')) {
            $keys = collect($this->compose->services)->keys()->map(function ($s) {
                return strtoupper($s);
            })->toArray();
            foreach ($keys as $key) {
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
        // strip everything else
        if (! Str::contains($line, '=')) {
            return false;
        }

        // if
        $key = substr($line, 0, strpos($line, '='));
        if (! isset($this->compose->matchedLaradockEnvs[$key])) {
            return false;
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
        if (Str::endsWith($key, '_LOG_PATH')) {
            $value = str_replace('./logs/', config('laradock.runtime_folder'), $value);
        } elseif (Str::endsWith($key, 'PATH')) {
            $value = config('laradock.context').'/'.str_replace('./', '', $value);
        }
        if ($key === 'MYSQL_ENTRYPOINT_INITDB') {
            $value = config('laradock.context').'/mysql/docker-entrypoint-initdb.d';
        }
        if ($key === 'WORKSPACE_INSTALL_YARN') {
            $value = \Illuminate\Support\Facades\File::exists(workingDirectory('yarn.lock'));
        }
        if ($key === 'WORKSPACE_INSTALL_NODE') {
            $value = \Illuminate\Support\Facades\File::exists(workingDirectory('package.json'));
        }
        if ($key === 'WORKSPACE_INSTALL_NPM_GULP') {
            $value = \Illuminate\Support\Facades\File::exists(workingDirectory('gulp.json'));
        }

        // set the default php version based on CLI php version
        if ($key === 'PHP_VERSION') {
            if (PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION <= 3) {
                $value = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
            }
        }
        // we shouldn't override the values
        if (isset($this->compose->laradockAttributes[$key])) {
            $value = $this->compose->laradockAttributes[$key];
        } elseif (isset($this->compose->envAttributes[$key])) {
            $value = $this->compose->envAttributes[$key];
        }
        // if the value has a space we need to wrap it in double-quotes
        if (Str::contains($value, ' ') && ! Str::startsWith($value, '"')) {
            $value = '"'.$value.'"';
        }

        return $key.'='.$value;
    }
}
