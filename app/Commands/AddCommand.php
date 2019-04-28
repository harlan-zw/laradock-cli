<?php

namespace Laradock\Commands;

use Laradock\Service\Laradock;
use LaravelZero\Framework\Commands\Command;

class AddCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'add 
    {service : The name of the service to add (required)}
    {--context= : The location of your docker files (optional)}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Add a Laradock service to your project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Laradock $laradock)
    {
        $service = $this->argument('service');

        if (empty($laradock->getOurDockerCompose())) {
            $this->error('Looks like you don\'t have a docker-compose.yml setup. Please run ./laradock setup');

            return;
        }

        // if it already exists within their docker-compose.yaml file we should confirm the re-add
        if (
            $laradock->hasService($service) &&
            ! $this->confirm('It looks like you already have a '.$service.' service. Would you like to re-add it?')) {
            return;
        }

        if (! $laradock->addService($service)) {
            $this->error('Invalid service: '.$service);

            return;
        }

        $this->call('status');
    }
}
