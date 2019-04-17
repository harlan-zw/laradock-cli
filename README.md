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

## Highlights

- Keep your project directory clean. Only have services you're using committed.
- Keep your .env file clean. Moves all laradock environment variables to their own `laradock-env` file.
- Make it easy to update your laradock between versions
- Make it super easy to manage your services
- Fix user / group id issues automatically
- Avoid duplicate configuration (Site URL, etc)
- Abstract docker functions (mounting, up, etc)
- Handle alternative laradock paths without headaches

------

## Installation

Via Composer

``` bash
composer require --dev loonpwn/laradock-cli
```

Add to .gitignore
`.laradock-env`

## Usage

### New Laravel Projects

1. Setup files `./vendor/bin/laradock init`
2. Install & Start docker containers. Note that this may take quite a few minutes. `./vendor/bin/laradock up`
3. Once you're up and running you can mount on to workspace with `./vendir/bin/laradock mount`

### Existing projects



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
