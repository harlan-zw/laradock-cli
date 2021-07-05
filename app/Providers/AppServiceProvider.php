<?php

namespace Laradock\Providers;

use Illuminate\Support\ServiceProvider;
use Laradock\Service\Laradock;

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
