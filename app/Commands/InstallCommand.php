<?php

namespace Laradock\Commands;

use Spatie\Emoji\Emoji;
use Laradock\Service\Laradock;
use Laradock\Service\BaseCommand;
use Laradock\Tasks\ParseDotEnvFile;
use Illuminate\Support\Facades\File;
use function Laradock\getDockerComposePath;
use Laradock\Tasks\CheckDockerComposeYamlExists;

class InstallCommand extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Initialize Laradock in your project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->title('Welcome to the Laradock CLI '.config('app.version'));

        $env = \Laradock\invoke(new ParseDotEnvFile());

        $envs = [
            'APP_URL',
            'APP_NAME',
            'DB_CONNECTION',
            'BROADCAST_DRIVER',
            'CACHE_DRIVER',
            'QUEUE_CONNECTION',
            'SESSION_DRIVER',
        ];
        $lines = [];
        foreach ($envs as $key) {
            $lines[] = [$key, $env[$key]];
        }
        $this->table([Emoji::wrench().' Key', 'Value'], $lines);

        if (! $this->confirmContinue(
            'Laradock CLI will be setup based on the above configuration',
            true
        )) {
            $this->info('Please update your .env file for the correct configuration.');

            return;
        }

        $didReinstall = false;
        if (\Laradock\invoke(new CheckDockerComposeYamlExists)) {
            $this->warn('Detected an existing docker-compose.yml file!');
            if (! $this->confirmContinue(
                'Continuing will modify your existing docker-compose files',
                false
            )) {
                return;
            }
            $this->info('Continuing with existing setup. Making sure current containers are down.');
            $this->call('down');
            $didReinstall = true;
        }

        if (! File::exists(\Laradock\workingDirectory('.env'))) {
            $this->warn('No .env file found!');
            if (! $this->confirmContinue(
                'You are missing an .env file which is required to automatically configure your services',
                false
            )) {
                return;
            }
        }

        $laradock = new Laradock();

        $envFolder = \Laradock\workingDirectory('env');
        // delete existing folders and files
        if (! $laradock->cleanup()) {
            $this->error('Failed to remove old Laradock files. Please delete them manually before continuing.');

            return;
        } else {
            $this->success('Removed old Laradock files.');
        }
        // create new files
        touch(getDockerComposePath());
        File::makeDirectory($envFolder, 0755, true, true);

        // add the default services
        $servicesAvailableToAdd = collect($laradock->services())->filter(function ($v) {
            return ! in_array($v, config('laradock.default_services'));
        })->toArray();
        $selectedServices = config('laradock.default_services');
        foreach ($selectedServices as $service) {
            $laradock->addService($service);
            $this->success('Added default service '.$service.'.');
        }

        // look at the drivers to figure out what services we need
        collect($env)->only([
            'DB_CONNECTION',
            'BROADCAST_DRIVER',
            'CACHE_DRIVER',
            'SESSION_DRIVER',
        ])->filter(function ($v) use ($laradock) {
            return $laradock->isValidService($v);
        })->each(function ($v, $k) use ($laradock, $env) {
            if ($this->confirm('The '.$k.' is setup for '.$v.'. Would you like to add the '.$v.' service?', true)) {
                $laradock->addService($v);
                $selectedServices[] = $v;
                $this->success('Added service '.$v.'.');
            }
        });

        if ($env['QUEUE_CONNECTION'] !== 'sync') {
            if ($this->confirm('It looks like you are using a queue. Would you like to add the php-worker service?', true)) {
                $laradock->addService('php-worker');
                $selectedServices[] = 'php-worker';
                $this->success('Added service php-worker.');
            }
        }

        $webserver = false;
        if (! in_array('apache2', $selectedServices) && ! in_array('nginx', $selectedServices)) {
            $selectedService = $this->choice(
                Emoji::questionMark().
                ' What web server would you like to use?',
                [
                    'apache2',
                    'nginx',
                    'none',
                ]
            );
            if ($selectedServices !== 'none') {
                $webserver = $selectedService;
                $selectedServices[] = $selectedService;
                $laradock->addService($selectedService);
                $this->success('Added service '.$selectedService.'.');

                $this->warn('Please note that only http is setup, you will manually need to setup https.');
            }
        }

        while ($this->confirm('Would you like to add another service?', true)) {
            $selectedService = $this->anticipate(
                'What service would you like to add? (leave blank to finish)',
                $servicesAvailableToAdd
            );
            if (! empty($selectedService)) {
                $laradock->addService($selectedService);
                $this->success('Added service '.$selectedService.'.');
                $selectedServices[] = $selectedService;
                $this->info('Selected services: '.implode(', ', $selectedServices));
            } else {
                break;
            }
        }

        $this->call('status');

        $this->bigSuccess('Install is complete! Get started with: `laradock`.');

        if (! empty($webserver)) {
            $this->warn('Remember to update your hosts file `127.0.0.1    '.str_replace(['http://', 'https://'], '', $env['APP_URL']).'`');
        }

        if ($didReinstall) {
            $this->warn('Since you reinstalled its a good idea to rebuild the containers with `laradock build`');
        }

        $this->info('If you have any docker related issues please refer to https://laradock.io/.');
    }
}
