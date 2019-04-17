<p align="center">
    <img title="Laradock CLI" height="91" src="https://github.com/loonpwn/laradock-cli/raw/master/assets/images/laradock-cli-logo.png" />
</p>


[![Total Downloads](https://img.shields.io/packagist/vpre/loonpwn/laradock-cli.svg?style=flat)](https://packagist.org/packages/loonpwn/laradock-cli)
[![Total Downloads](https://img.shields.io/packagist/dt/loonpwn/laradock-cli.svg?style=flat)](https://packagist.org/packages/loonpwn/laradock-cli)
[![StyleCI](https://github.styleci.io/repos/155632347/shield?branch=master)](https://github.styleci.io/repos/155632347)

Note: This package is in active development. A release is coming soon where it will be usable, for now it's just work in progress commits. 

Laradock CLI is an unofficial package built with [Laravel Zero](https://laravel-zero.com/). It's built on top of [Laradock](https://laradock.io/) to address common issues
and workflows that you may come accross. The high-level goal of the package is to be able to setup a Laradock project in under 2 minutes 
and only commit code that is essential.

## Features

#### Clean project

Laradock CLI is built for the people who like tidy projects. Whenever you interact with Laradock CLI it will always try and add the minimal amount of configuration and files required.


#### Clean .env*

Moves all Laradock environment variables to their own `laradock-env` file.

#### No configuration

Laradock CLI reads your project like a book. The end goal is a zero-configuration docker setup. Some examples:
- Fixes User and Group IDs
- Checks for package.json before installing node in workspace
- Checks your CLI php version for which php version to use
- Updates your environment variables for docker credentials (coming soon)
- Updates host files for SITE_URL (coming soon)

#### Easier Upgrades

Simple command to add additional services, Laradock CLI takes care of all files and configurations for you.

#### Your Laradock

Easily choose where you want to install the files, don't need locked into the laradock folder.


------

## Installation

Via Composer

``` bash
composer require --dev loonpwn/laradock-cli
```

Add to .gitignore
`.laradock-env`

## Usage

1. Setup files `./vendor/bin/laradock init`
2. Install & Start docker containers. Note that this may take quite a few minutes. `./vendor/bin/laradock up`
3. Once you're up and running you can mount on to workspace with `./vendir/bin/laradock mount`

## Documentation

### Paths

Laradock CLI works slightly different to Laradock in terms of its paths. Laradock CLI out of the box exists in your `env/docker` folder.

You are welcome to change the context of your laradock folder by setting an environment variable.

`LARADOCK_CLI_PATH=./laradock/`

### Commands

- `laradock init` 
This will start laradock and add the default services and guide you through adding more services.
- `laradock status` 
See which services you're currently using
- `laradock services` 
List all laradock services
- `laradock add <service>` 
Add a specific service
- `laradock remove <service>` 
Remove a specific service
- `laradock up` 
Runs `docker-compose up -d` with the `laradock-env` loaded in.
- `laradock down` 
Runs `docker-compose down` with the `laradock-env` loaded in.
- `laradock workspace` 
Mounts yourself to the workspace container as Laradock user
