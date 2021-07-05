<?php

namespace Laradock\Tasks;

use Illuminate\Support\Facades\File;

class SetupPHPWorker
{
    public function __invoke($env)
    {
        // modify the database createdb.sql
        $confFilePath = \Laradock\getServicesPath('php-worker').'/supervisord.d/';
        File::copy($confFilePath.'laravel-scheduler.conf.example', $confFilePath.'laravel-scheduler.conf');
        File::copy($confFilePath.'laravel-worker.conf.example', $confFilePath.'laravel-worker.conf');
    }
}
