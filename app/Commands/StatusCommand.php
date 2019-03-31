<?php

namespace App\Commands;

use App\Tasks\ParseDockerComposeYaml;
use LaravelZero\Framework\Commands\Command;

class StatusCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'status';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Check the status of your Laradock configuration.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $laradockCompose = invoke(new ParseDockerComposeYaml());

        $this->table(['Service', 'Context'], collect($laradockCompose['services'])->map(function($service, $key) {
            return [$key, $service['build']['context'] ?? $service['build']];
        }));
    }

}
