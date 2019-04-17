<?php

namespace Laradock\Commands;

use Laradock\Service\Laradock;
use LaravelZero\Framework\Commands\Command;

class RemoveCommand extends Command
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

        $laradock->removeService($service);

        $this->call('status');
    }
}
