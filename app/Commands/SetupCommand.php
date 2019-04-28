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

class SetupCommand extends Command
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
        if (\Laradock\invoke(new CheckDockerComposeYamlExists)) {
            $this->warn('It looks like you may have already installed Laradock,');
            if (! $this->confirm(
                'Continuing will revert your current installation. Would you like to continue?',
                false
            )) {
                return;
            }
        }
        if (! $this->confirm(
            'Laradock CLI reads your .env file to setup. Is your .env file up to date?',
            true
        )) {
            return;
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
        })->each(function ($v, $k) use ($laradock, $env) {
            if (! $laradock->hasService($v)) {
                if ($this->confirm('It looks like you are using '.$v.'. Would you like to enable the '.$v.' service?', true)) {
                    $laradock->addService($v);
                    $selectedServices[] = $v;
                    $this->info(Emoji::heavyCheckMark().' Enabling service '.$v.' because of '.$k.' from .env.');
                    if ($v === 'mysql') {
                        $confFilePath = \Laradock\getServicesPath('mysql').'/docker-entrypoint-initdb.d/';
                        $sqlFile = $confFilePath.'createdb.sql';
                        File::copy($confFilePath.'createdb.sql.example', $sqlFile);
                        file_put_contents($sqlFile, implode('',
                            array_map(function ($data) use ($env) {
                                if (stristr($data, '#CREATE DATABASE IF NOT EXISTS `dev_db_1` COLLATE \'utf8_general_ci\' ;')) {
                                    $this->info('Setting mysql DB_DATABASE to '.$env['DB_DATABASE']);

                                    return 'CREATE DATABASE IF NOT EXISTS `'.$env['DB_DATABASE'].'` COLLATE \'utf8_general_ci\' ; '."\n";
                                }
                                if (stristr($data, '#GRANT ALL ON `dev_db_1`.*')) {
                                    $this->info('Setting mysql DB_USERNAME to '.$env['DB_USERNAME']);

                                    return 'GRANT ALL ON `'.$env['DB_DATABASE'].'`.* TO \''.$env['DB_USERNAME'].'\'@\'%\' ;';
                                }

                                return $data;
                            }, file($sqlFile))
                        ));
                    }
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
                if ($selectedService === 'apache2') {
                    $confFile = \Laradock\getServicesPath('apache2').'/sites/default.apache.conf';
                    $url = str_replace(['http://', 'https://'], '', $env['APP_URL']);
                    file_put_contents($confFile, implode('',
                        array_map(function ($data) use ($url) {
                            if (stristr($data, 'laradock.test')) {
                                return '  ServerName '.$url."\n";
                            }
                            if (stristr($data, 'DocumentRoot /var/www/')) {
                                return '  DocumentRoot /var/www/public '."\n";
                            }
                            if (stristr($data, '<Directory "/var/www/">')) {
                                return '  <Directory "/var/www/public"> '."\n";
                            }

                            return $data;
                        }, file($confFile))
                    ));
                    $this->info(Emoji::heavyCheckMark().' Configured apache2 for site '.$url);
                }
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

        $this->info(Emoji::confettiBall().' Laradock CLI setup is complete. Please make sure:');
        $this->info('- You update your hosts file `127.0.0.1    ' . str_replace(['http://', 'https://'], '', $env['APP_URL']) . '`');
        $this->info('- Make sure all your variables are setup correctly in `.laradock-env`');
        $this->info('- Read the Laradock documentation');

        $this->comment('Get started with: `./laradock`');
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
