<?php

namespace Laradock\Tasks;

use Illuminate\Support\Facades\Log;

class SetupNginx
{
    public function __invoke($env)
    {
        $confFile = \Laradock\getServicesPath('nginx').'/sites/default.conf';
        $url = str_replace(['http://', 'https://'], '', $env['APP_URL']);
        file_put_contents($confFile, implode('',
            array_map(function ($data) use ($url) {
                if (false !== stripos($data, 'localhost;')) {
                    return '    server_name '.$url.';'."\n";
                }

                return $data;
            }, file($confFile))
        ));
        Log::info('Configured nginx for site '.$url);
    }
}
