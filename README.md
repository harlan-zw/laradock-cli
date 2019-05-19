<p align="center">
    <img title="Laradock CLI" height="91" src="https://github.com/loonpwn/laradock-cli/raw/master/assets/images/laradock-cli-logo.png" />
</p>


[![Total Downloads](https://img.shields.io/packagist/vpre/loonpwn/laradock-cli.svg?style=flat)](https://packagist.org/packages/loonpwn/laradock-cli)
[![Total Downloads](https://img.shields.io/packagist/dt/loonpwn/laradock-cli.svg?style=flat)](https://packagist.org/packages/loonpwn/laradock-cli)
[![StyleCI](https://github.styleci.io/repos/174919610/shield?branch=master)](https://github.styleci.io/repos/174919610)

Laradock CLI is a package built with [Laravel Zero](https://laravel-zero.com/), on top of [Laradock](https://laradock.io/). 

**Mission** Make docker easy and accessible for all Laravel & PHP developers

**Goal** To be able to setup Laradock (docker) on new and existing Laravel projects within 2 minutes.

## Features

#### Clean project

Laradock CLI is built for the people who like tidy projects. Whenever you interact with Laradock CLI it will always try and add the minimal amount of configuration and files required. 
Since it is just a .phar file you only need one laradock CLI installed for all your projects.

#### Clean .env

Moves all Laradock environment variables to their own `.env.laradock` file. No longer have a 300 line .env file.

#### Zero Configuration Setup

Laradock CLI reads your `.env` and makes smart assumptions to reduce a lot of boilerplate configuration. Some examples:
- Fixes User and Group IDs
- Checks for package.json before installing node in workspace
- Checks your CLI php version for which php version to use
- Modifies the apache2 vhost site URL
- Sets up your MySQL service with a database

#### Easier Upgrades

Simple command to add or remove services, Laradock CLI takes care of all the heavy lifting of copying files and cofiguration.

#### Much More

This project is in early development and has lots of planned updates coming.


------

## Installation

### Phar

``` bash
wget https://github.com/loonpwn/laradock-cli/releases/download/0.4.0/laradock
chmod +x ./laradock
```

_Recommended: `sudo mv laradock /usr/bin/laradock`_

### Composer (alternative)


``` bash
composer global require loonpwn/laradock-cli
```

## Usage

1. `laradock setup` Run the setup tool
2. Check the `.env.laradock` and the files within `./env/docker` has the correct configuration for your project.
3. `laradock` Build and run the containers and then mount to the workspace container.

## Documentation

### Environment Configuration

Laradock CLI works slightly different to Laradock in terms of its paths, they can be configured using environment variables.

#### `LARADOCK_CLI_PATH`

_Default: `./env/docker`_

Where Laradock CLI will put the files to build your docker containers.

#### `LARADOCK_CLI_RUNTIME_PATH`

_Default: `./storage/docker`_

Where Laradock CLI will put the runtime files generated from your docker containers.

### Laradock Commands

- `laradock` 
This will start docker with `docker-compose up -d` and then mount you on the workspace container.
- `laradock setup` 
This will start laradock and add the default services and guide you through adding more services.
- `laradock status` 
See which services you're currently using
- `laradock services` 
List all laradock services
- `laradock add <service>` 
Add a specific service
- `laradock remove <service>` 
Remove a specific service
- `laradock workspace` 
Mounts yourself to the workspace container as Laradock user

### DockerCompose Commands

- `laradock up` 
Runs `docker-compose up -d` with the `laradock-env` loaded in.
- `laradock down` 
Runs `docker-compose down` with the `laradock-env` loaded in.
- `laradock build` 
Runs `docker-compose build` with the `laradock-env` loaded in.
- `laradock restart` 
Runs `docker-compose restart` with the `laradock-env` loaded in.
- `laradock push` 
Runs `docker-compose push` with the `laradock-env` loaded in.
- `laradock exec` 
Runs `docker-compose exec` with the `laradock-env` loaded in.
