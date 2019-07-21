<?php

namespace Laradock\Tasks;

use Laradock\Service\Laradock;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SetupMariaDB
{
    const USER = 'default';
    const PASSWORD = 'secret';

    public function __invoke($env)
    {
        // modify the database createdb.sql
        $confFilePath = \Laradock\getServicesPath('mariadb').'/docker-entrypoint-initdb.d/';
        $sqlFile = $confFilePath.'createdb.sql';
        File::copy($confFilePath.'createdb.sql.example', $sqlFile);
        file_put_contents($sqlFile, implode('',
            array_map(function ($data) use ($env) {
                if (false !== stripos($data, '#CREATE DATABASE IF NOT EXISTS `dev_db_1` COLLATE \'utf8_general_ci\' ;')) {
                    Log::info('Setting mysql DB_DATABASE to '.$env['DB_DATABASE']);

                    return 'CREATE DATABASE IF NOT EXISTS `'.$env['DB_DATABASE'].'` COLLATE \'utf8_general_ci\'; '."\n".
                     "alter user '".self::USER."'@'%' identified with mysql_native_password by '".self::PASSWORD."';"."\n";
                }
                if (false !== stripos($data, '#GRANT ALL ON `dev_db_1`.*')) {
                    Log::info('Setting mysql DB_USERNAME to '.self::USER);

                    return 'GRANT ALL ON `'.$env['DB_DATABASE'].'`.* TO \''.self::USER.'\'@\'%\' ;';
                }

                return $data;
            }, file($sqlFile))
        ));
        // modify the .env file
        file_put_contents(\Laradock\getDotEnvPath(), implode('',
            array_map(function ($data) use ($env) {
                if (false !== stripos($data, 'DB_USERNAME')) {
                    Log::info('Setting .env DB_USERNAME to '.self::USER);

                    return 'DB_USERNAME='.self::USER."\n";
                }
                if (false !== stripos($data, 'DB_PASSWORD')) {
                    Log::info('Setting .env DB_PASSWORD to '.self::PASSWORD);

                    return 'DB_PASSWORD='.self::PASSWORD."\n";
                }
                if (false !== stripos($data, 'DB_HOST')) {
                    Log::info('Setting .env DB_HOST to mysql');

                    return 'DB_HOST=mysql'."\n";
                }

                return $data;
            }, file(\Laradock\getDotEnvPath()))
        ));
    }
}
