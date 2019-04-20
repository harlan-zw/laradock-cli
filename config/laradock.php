<?php

return [

    'compose_file' => 'docker-compose.yml',

    'laradock_path' => base_path('bin/laradock/'),

    'context' => env('LARADOCK_CLI_PATH', './env/docker'),

    'runtime_folder' => env('LARADOCK_CLI_RUNTIME_PATH', './storage/docker/'),

    'default_services' => [
        'workspace',
        'php-fpm',
        'docker-in-docker',
    ],
];
