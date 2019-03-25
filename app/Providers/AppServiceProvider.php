<?php

namespace App\Providers;

use App\Models\DockerCompose;
use App\Service\Laradock;
use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton(Laradock::class, function() {
            return new Laradock(config('laradock'));
        });
    }
}
