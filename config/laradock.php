<?php

return [

    'compose_file' => 'docker-compose.yml',

    'laradock_path' => vendor_path('laradock/laradock/docker-compose.yml'),

    'context' => './env/docker',

    'default_services' => [
        'workspace',
        'php-fpm',
        'docker-in-docker',
    ],
];
