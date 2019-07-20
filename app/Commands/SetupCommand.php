<?php

namespace Laradock\Commands;

use Laradock\Service\BaseCommand;
use Spatie\Emoji\Emoji;
use Laradock\Service\Laradock;
use Laradock\Tasks\ParseDotEnvFile;
use Illuminate\Support\Facades\File;
use function Laradock\getDockerComposePath;
use Laradock\Tasks\CheckDockerComposeYamlExists;

class SetupCommand extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'setup';

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

        $this->line('');
        $this->info('Welcome to the Laradock CLI setup tool. This is currently in alpha, please report any issues.');
        $this->line('');

        if (\Laradock\invoke(new CheckDockerComposeYamlExists)) {
            $this->warn('Detected an existing docker-compose.yml file!');
            if (! $this->confirmContinue(
                'Continuing will modify your existing docker-compose files',
                false
            )) {
                return;
            }
        }

        if (!File::exists(\Laradock\workingDirectory('.env'))) {
            $this->warn('No .env file found!');
            if (! $this->confirmContinue(
                'You are missing an .env file which is required to automatically configure your services',
                false
            )) {
                return;
            }
        }

        // Reset the existing setup
        $envFolder = \Laradock\workingDirectory('env');
        File::delete(getDockerComposePath());
        touch(getDockerComposePath());
        File::deleteDirectory($envFolder, false);
        File::makeDirectory($envFolder, 0755, true, true);

        // add the default services
        $laradock = new Laradock();
        $servicesAvailableToAdd = collect($laradock->services())->filter(function ($v) {
            return ! in_array($v, config('laradock.default_services'));
        })->toArray();
        $selectedServices = config('laradock.default_services');
        foreach ($selectedServices as $service) {
            $laradock->addService($service);
            $this->success('Added default service ' . $service . '.');
        }

        // look at the drivers to figure out what services we need
        $env = \Laradock\invoke(new ParseDotEnvFile());
        collect($env)->only([
            'DB_CONNECTION',
            'BROADCAST_DRIVER',
            'CACHE_DRIVER',
            'SESSION_DRIVER',
        ])->filter(function ($v) use ($laradock) {
            return $laradock->isValidService($v);
        })->each(function ($v, $k) use ($laradock, $env) {
            if ($laradock->hasService($v)) {
                return;
            }
            if ($this->confirm('The ' . $k . ' is setup for '.$v.'. Would you like to add the '.$v.' service?', true)) {
                $laradock->addService($v);
                $selectedServices[] = $v;
                $this->success('Added service ' . $v . '.');
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
                Emoji::questionMark() .
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
                $this->success('Added service ' . $selectedService . '.');
            }
        }

        while ($this->confirm('Would you like to add another service?', true)) {
            $selectedService = $this->anticipate(
                'What service would you like to add? (leave blank to finish)',
                $servicesAvailableToAdd
            );
            if (! empty($selectedService)) {
                $laradock->addService($selectedService);
                $this->success('Added service ' . $selectedService . '.');
                $selectedServices[] = $selectedService;
                $this->info('Selected services: '.implode(', ', $selectedServices));
            } else {
                break;
            }
        }

        $this->call('status');

        $this->bigSuccess('Setup is complete. You will need to complete the following manual steps to finish:');

        if (!empty($webserver)) {
            $this->line('- Update your hosts file `127.0.0.1    ' . str_replace(['http://', 'https://'], '', $env['APP_URL']) . '`');
        }
        $this->line('- Double check your .env.laradock and .env have the correct configuration.');
        $this->line('- Double check your docker-compose.yml configuration.');

        $this->comment('Get started with: `./laradock`. If you have any docker related issues please refer to https://laradock.io/.');
    }

}
