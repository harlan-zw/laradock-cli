<?php

namespace Laradock\Commands;

use Laradock\Service\BaseCommand;
use Laradock\Service\Laradock;

class RemoveCommand extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'remove
     {service : The name of the service to remove (required)}
     {--context= : The location of your docker files (optional)}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove a Laradock service from your project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Laradock $laradock)
    {
        $service = $this->argument('service');

        $this->info('Removing a service requires stopping containers, starting shut down.');

        $this->call('down');

        $this->info('Now removing service '.$service.'.');

        $laradock->removeService($service);

        $this->call('status');
    }
}
