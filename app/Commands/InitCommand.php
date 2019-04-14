<?php

namespace App\Commands;

use App\Models\DockerCompose;
use App\Service\Laradock;
use App\Tasks\CheckDockerComposeYamlExists;
use App\Tasks\ParseDockerComposeYaml;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
use Spatie\Emoji\Emoji;

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
    public function handle(Laradock $laradock)
    {
        if (\invoke(new CheckDockerComposeYamlExists)) {
            $this->warn('You have already installed laradock.');
        }

        $envFolder = base_path('env');
        Log::info('Making directory ' . $envFolder);
        File::delete(['docker-compose.yml']);
        File::deleteDirectory('env', false);
        File::makeDirectory($envFolder, 0755, true, true);
        touch(base_path('docker-compose.yml'));

        $servicesAvailableToAdd = collect($laradock->services())->filter(function ($v) {
            return !in_array($v, config('laradock.default_services'));
        })->toArray();
        $selectedServices = config('laradock.default_services');
        foreach($selectedServices as $service) {
            $laradock->addService($service);
            $this->info(Emoji::heavyCheckMark() . ' We have enabled ' . $service . ' service for you.');
        }

        while ($this->confirm('Would you like to add another service?', true)) {
            $selectedService = $this->choice(
                'What service would you like to enable?',
                $servicesAvailableToAdd
            );
            $laradock->addService($selectedService);
            $this->info(Emoji::heavyCheckMark() . ' Added service ' . $selectedService);
            $selectedServices[] = $selectedService;
            $this->info(Emoji::notebook() . ' Selected services: ' . implode(', ', $selectedServices));
        }

        $this->call('status');

        $this->info(Emoji::confettiBall() . ' Laradock is finished. Get started with:');

        $this->comment('./laradock up');
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
