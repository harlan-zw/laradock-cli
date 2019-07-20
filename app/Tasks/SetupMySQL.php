<?php


namespace Laradock\Tasks;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SetupMySQL {

    public function __invoke($env) {
        $confFilePath = \Laradock\getServicesPath('mysql').'/docker-entrypoint-initdb.d/';
        $sqlFile = $confFilePath.'createdb.sql';
        File::copy($confFilePath.'createdb.sql.example', $sqlFile);
        file_put_contents($sqlFile, implode('',
            array_map(function ($data) use ($env) {
                if (false !== stripos($data, '#CREATE DATABASE IF NOT EXISTS `dev_db_1` COLLATE \'utf8_general_ci\' ;')) {
                    Log::info('Setting mysql DB_DATABASE to '.$env['DB_DATABASE']);

                    return 'CREATE DATABASE IF NOT EXISTS `'.$env['DB_DATABASE'].'` COLLATE \'utf8_general_ci\' ; '."\n";
                }
                if (false !== stripos($data, '#GRANT ALL ON `dev_db_1`.*')) {
                    Log::info('Setting mysql DB_USERNAME to '.$env['DB_USERNAME']);

                    return 'GRANT ALL ON `'.$env['DB_DATABASE'].'`.* TO \''.$env['DB_USERNAME'].'\'@\'%\' ;';
                }

                return $data;
            }, file($sqlFile))
        ));
    }
}
