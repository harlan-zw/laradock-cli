<?php

namespace App\Commands;

use App\Tasks\CheckDockerComposeYamlExists;
use App\Tasks\ParseDockerComposeYaml;
use Dotenv\Dotenv;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
use Spatie\Emoji\Emoji;
use Symfony\Component\Yaml\Yaml;

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
        if (\invoke(new CheckDockerComposeYamlExists)) {
            $this->error('You have already installed laradock.');
        }

        // bootstrap files / paths
        $envFolder = base_path('env');
        Log::info('Making directory ' . $envFolder);
        File::delete(['docker-compose.yml']);
        File::deleteDirectory('env', false);
        File::makeDirectory($envFolder, 0755, true, true);
        touch(base_path('docker-compose.yml'));

        $laradockCompose = invoke(new ParseDockerComposeYaml(vendor_path('laradock/laradock/docker-compose.yml')));

//        $dotenv = Dotenv::create(vendor_path('laradock/laradock'), 'env-example');
//        dd($dotenv->load());

        $ourCompose = invoke(new ParseDockerComposeYaml()) ?? [];

        $config = array_merge($ourCompose, collect($laradockCompose)->filter(function ($val, $key) {
            // by default we will always bind the version & networks of laradock
            return in_array($key, ['version', 'networks']);
        })->toArray());
        $config['services']['workspace'] = $laradockCompose['services']['workspace'];

        $servicesAvailableToAdd = collect($laradockCompose['services'])->keys()->filter(function ($v) {
            return $v !== 'workspace';
        })->toArray();
        $selectedServices = ['workspace'];
        $this->info(Emoji::heavyCheckMark() . ' We have enabled the workspace container for you.');

        while ($this->confirm('Would you like to add another service?', true)) {
            $selectedService = $this->choice(
                'What service would you like to enable?',
                $servicesAvailableToAdd
            );
            $config['services'][$selectedService] = $laradockCompose['services'][$selectedService];
            $this->info(Emoji::hammerAndPick() . ' Added service ' . $selectedService);
            $selectedServices[] = $selectedService;
            $this->info('Selected services: ' . implode(', ', $selectedServices));
        }

        // bind the select service
        // bind the networks
        file_put_contents(base_path('docker-compose.yml'), Yaml::dump($config, 6, 2));

        collect($selectedServices)->each(function($service) {
            File::copyDirectory(vendor_path('laradock/laradock/' . $service), base_path('env/' . $service));
        });

        $this->info(Emoji::confettiBall() . ' Laradock is finished. Get started with laradock up.');
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
