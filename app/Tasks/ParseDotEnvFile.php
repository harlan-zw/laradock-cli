<?php

namespace Laradock\Tasks;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ParseDotEnvFile
{
    private $dotEnv;

    /**
     * ParseDockerComposeYaml constructor.
     * @param bool|string $path
     */
    public function __construct($path = null, $file = null)
    {
        $path = $path ?? \Laradock\workingDirectory();
        if (! File::exists($path.'/'.$file)) {
            Log::warning('Missing '.$file.' file at '.$path);
        }
        $this->dotEnv = Dotenv::create($path ?? \Laradock\workingDirectory(), $file);
    }

    public function __invoke()
    {
        return $this->dotEnv->safeLoad();
    }
}
