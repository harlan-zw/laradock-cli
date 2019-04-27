<?php

namespace Laradock\Commands;

use Spatie\Emoji\Emoji;
use Laradock\Service\Laradock;
use Laradock\Tasks\ParseDotEnvFile;
use Illuminate\Support\Facades\File;
use function Laradock\getDockerComposePath;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Laradock\Tasks\CheckDockerComposeYamlExists;

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init';

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
        if (\Laradock\invoke(new CheckDockerComposeYamlExists)) {
            $this->warn('It looks like you may have already installed Laradock,');
            if (! $this->confirm(
                'Continuing will revert your current installation. Would you like to continue?',
                false
            )) {
                return;
            }
        }

        $envFolder = \Laradock\workingDirectory('env');
        File::delete(getDockerComposePath());
        touch(getDockerComposePath());
        File::deleteDirectory($envFolder, false);
        File::makeDirectory($envFolder, 0755, true, true);

        $laradock = new Laradock();
        $servicesAvailableToAdd = collect($laradock->services())->filter(function ($v) {
            return ! in_array($v, config('laradock.default_services'));
        })->toArray();
        $selectedServices = config('laradock.default_services');
        foreach ($selectedServices as $service) {
            $laradock->addService($service);
            $this->info(Emoji::heavyCheckMark().' Default service '.$service.' enabled.');
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
        })->each(function ($v, $k) use ($laradock) {
            if (! $laradock->hasService($v)) {
                if ($this->confirm('It looks like you are using '.$v.'. Would you like to enable the '.$v.' service?', true)) {
                    $laradock->addService($v);
                    $selectedServices[] = $v;
                    $this->info(Emoji::heavyCheckMark().' Enabling service '.$v.' because of '.$k.' from .env.');
                }
            }
        });
        if ($env['QUEUE_CONNECTION'] !== 'sync') {
            if ($this->confirm('It looks like you are using a queue. Would you like to enable the php-worker service?', true)) {
                $laradock->addService('php-worker');
                $selectedServices[] = 'php-worker';
            }
        }

        if (! in_array('apache2', $selectedServices) && ! in_array('nginx', $selectedServices)) {
            $selectedService = $this->choice(
                'Would you like to enable a web server?',
                [
                    'apache2',
                    'nginx',
                    'No',
                ]
            );
            if ($selectedServices !== 'No thanks') {
                $selectedServices[] = $selectedService;
                $laradock->addService($selectedService);
                $this->info(Emoji::heavyCheckMark().' Added service '.$selectedService);
            }
        }

        while ($this->confirm('Would you like to add another service?', true)) {
            $selectedService = $this->anticipate(
                'What service would you like to enable? (leave blank to skip)',
                $servicesAvailableToAdd
            );
            if (! empty($selectedService)) {
                $laradock->addService($selectedService);
                $this->info(Emoji::heavyCheckMark().' Added service '.$selectedService);
                $selectedServices[] = $selectedService;
                $this->info(Emoji::notebook().' Selected services: '.implode(', ', $selectedServices));
            }
        }

        $this->call('status');

        $this->info(Emoji::confettiBall().' Laradock is finished. Get started with:');

        $this->comment('./laradock');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
