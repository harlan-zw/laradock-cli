<?php

return [

    'compose_file' => 'docker-compose.yml',

    'laradock_path' => base_path('bin/laradock-7.14/'),

    'context' => env('LARADOCK_CLI_PATH', './env/docker'),

    'default_services' => [
        'workspace',
        'php-fpm',
        'docker-in-docker',
    ],
];
