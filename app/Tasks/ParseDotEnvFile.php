<?php

namespace Laradock\Tasks;

use Dotenv\Dotenv;

class ParseDotEnvFile
{
    private $dotEnv;

    /**
     * ParseDockerComposeYaml constructor.
     * @param bool|string $path
     */
    public function __construct($path = null, $file = null)
    {
        $this->dotEnv = Dotenv::create($path ?? \Laradock\workingDirectory(), $file);
    }

    public function __invoke()
    {
        return $this->dotEnv->safeLoad();
    }
}
