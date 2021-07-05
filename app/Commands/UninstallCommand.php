<?php

namespace Laradock\Commands;

use Laradock\Service\BaseCommand;
use Laradock\Service\Laradock;

class UninstallCommand extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'uninstall';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Removes Laradock CLI files from your project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Laradock $laradock)
    {
        if (! $this->confirmContinue('This will remove all Laradock CLI files')) {
            return;
        }

        $this->info('Removing Laradock CLI files.');
        $laradock->cleanup();

        $this->success('Laradock CLI files have been removed.');

        $this->warn('Due to user permissions, you may need to manually remove the storage folder at: ./storage/docker');
    }
}
