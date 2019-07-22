<?php

namespace Laradock\Tasks;

use Laradock\Service\Laradock;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SetupPostgres
{
    const USER = 'pguser';
    const PASSWORD = 'secret';

    public function __invoke($env)
    {
        // modify the database createdb.sql
        $confFilePath = \Laradock\getServicesPath('postgres').'/docker-entrypoint-initdb.d/';
        $sqlFile = $confFilePath.'createdb.sh';
        File::copy($confFilePath.'createdb.sh.example', $sqlFile);
        file_put_contents($sqlFile, '
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER '.self::USER.' WITH ENCRYPTED PASSWORD \''.self::PASSWORD.'\';
    CREATE DATABASE "'.$env['DB_DATABASE'].'";
    GRANT ALL PRIVILEGES ON DATABASE '.$env['DB_DATABASE'].' TO '.self::USER.';
EOSQL
');
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
                    Log::info('Setting .env DB_HOST to postgres');

                    return 'DB_HOST=postgres'."\n";
                }

                if (false !== stripos($data, 'DB_PORT')) {
                    Log::info('Setting .env DB_PORT to 5432');

                    return 'DB_PORT=5432'."\n";
                }

                return $data;
            }, file(\Laradock\getDotEnvPath()))
        ));
    }
}
