<?php

namespace App\Commands;

use App\Tasks\ParseDockerComposeYaml;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Spatie\Emoji\Emoji;
use Symfony\Component\Yaml\Yaml;

class AddCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'add';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Add a Laradock service to your project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle($service = 'php-worker', $context ='./env/docker/')
    {
        $ourCompose = invoke(new ParseDockerComposeYaml());

        $laradockCompose = invoke(new ParseDockerComposeYaml(vendor_path('laradock/laradock/docker-compose.yml')));

        $ourCompose['services'][$service] = $laradockCompose['services'][$service];

        // modify the context
        $ourCompose['services'][$service]['build']['context'] = str_replace('./', $context, $ourCompose['services'][$service]['build']['context']);
        // copy the directory over
        File::copyDirectory(vendor_path('laradock/laradock/' . $service), base_path($context . '/' . $service));

        file_put_contents(base_path('docker-compose.yml'), Yaml::dump($ourCompose, 6, 2));

        $this->table(['Service', 'Context'], collect($ourCompose['services'])->map(function($service, $key) {
            return [$key, $service['build']['context']];
        }));
    }

}
