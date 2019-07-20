<p align="center">
    <img title="Laradock CLI" height="91" src="https://github.com/loonpwn/laradock-cli/raw/master/assets/images/laradock-cli-logo.png" />
</p>


[![Total Downloads](https://img.shields.io/packagist/vpre/loonpwn/laradock-cli.svg?style=flat)](https://packagist.org/packages/loonpwn/laradock-cli)
[![Total Downloads](https://img.shields.io/packagist/dt/loonpwn/laradock-cli.svg?style=flat)](https://packagist.org/packages/loonpwn/laradock-cli)
[![StyleCI](https://github.styleci.io/repos/174919610/shield?branch=master)](https://github.styleci.io/repos/174919610)

Laradock CLI is a tool for Laravel projects which automates the docker configuration setup for services, amongst other things.

It is built with [Laravel Zero](https://laravel-zero.com/), on top of [Laradock](https://laradock.io/). 

## Features

#### Zero Configuration Docker

Laradock CLI reads your `.env` and makes smart assumptions to reduce a lot of boilerplate configuration. Some examples:
- Checks your driver settings and recommends which services are applicable
- Fixes User and Group IDs
- Checks for package.json before installing node in workspace
- Checks your CLI php version for which php version to use
- Modifies the apache2/nginx vhost site URL
- Sets up your MySQL service with a database

#### Clean project

Laradock tends to have a larger footprint for the amount of code you have to commit to your repository, Laradock CLI 
aims to fix this my only including the files for the services you are using.

#### Clean .env

All docker environment variables have been moved to their own `.env.laradock` file. No longer have a 300 line .env file.

#### Easier Maintenance

Simple command to add or remove services, Laradock CLI takes care of all the heavy lifting of setting up files, updating configuration, etc.

#### Much More

This project is in early development and has lots of planned updates coming.

------

## Installation

### Phar

``` bash
wget https://github.com/loonpwn/laradock-cli/releases/download/0.4.2/laradock
chmod +x ./laradock
```

_Recommended: `sudo mv laradock /usr/bin/laradock`_

## Usage

1. `laradock setup` Run the setup tool
2. Check the `.env.laradock` and the files within `./env/docker` has the correct configuration for your project.
3. `laradock` Build and run the containers and then mount to the workspace container.

## Documentation

### Laradock Commands

- `laradock` 
This will start docker with `docker-compose up -d` and then mount you on the workspace container.
- `laradock setup` 
An interactive guide for setting up your project with Laradock CLI.
- `laradock status` 
See which services you're currently using
- `laradock services` 
List all Laradock services
- `laradock add <service>` 
Add a specific service.
- `laradock remove <service>` 
Remove a specific service.
- `laradock workspace` 
Mounts yourself to the workspace container as Laradock user.

### DockerCompose Commands

- `laradock up` 
Runs `docker-compose up -d` with the `.env.laradock` loaded in.
- `laradock down` 
Runs `docker-compose down` with the `.env.laradock` loaded in.
- `laradock build` 
Runs `docker-compose build` with the `.env.laradock` loaded in.
- `laradock restart` 
Runs `docker-compose restart` with the `.env.laradock` loaded in.
- `laradock push` 
Runs `docker-compose push` with the `.env.laradock` loaded in.
- `laradock exec` 
Runs `docker-compose exec` with the `.env.laradock` loaded in.
- `laradock ps` 
Runs `docker-compose ps` with the `.env.laradock` loaded in.
