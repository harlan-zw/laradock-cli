<?php

namespace Laradock\Providers;

use Laradock\Commands\DockerComposeCommand;
use Laradock\Service\Laradock;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(Laradock::class, function () {
            return new Laradock();
        });

    }
}
