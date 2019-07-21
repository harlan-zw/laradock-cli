<?php

namespace Laradock\Commands;

use Laradock\Service\BaseCommand;
use Laradock\Service\Laradock;

class ListServicesCommand extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'services';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List the services you are able to enable';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Laradock $laradock)
    {
        $this->table(['Service'], collect($laradock->services())->map(function ($service) {
            return [$service];
        }));
    }
}
