<?php


namespace Laradock\Tasks;


use Illuminate\Support\Facades\Log;

class SetupApache2 {

    public function __invoke($env) {
        $confFile = \Laradock\getServicesPath('apache2').'/sites/default.apache.conf';
        $url = str_replace(['http://', 'https://'], '', $env['APP_URL']);
        file_put_contents($confFile, implode('',
            array_map(function ($data) use ($url) {
                if (false !== stripos($data, 'laradock.test')) {
                    return '  ServerName '.$url."\n";
                }
                if (false !== stripos($data, 'DocumentRoot /var/www/')) {
                    return '  DocumentRoot /var/www/public '."\n";
                }
                if (false !== stripos($data, '<Directory "/var/www/">')) {
                    return '  <Directory "/var/www/public"> '."\n";
                }

                return $data;
            }, file($confFile))
        ));
        Log::info('Configured apache2 for site ' . $url);
    }
}
