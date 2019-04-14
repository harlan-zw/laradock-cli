<p align="center">
    <img title="Laradock CLI" height="91" src="https://github.com/loonpwn/laradock-cli/raw/master/assets/images/laradock-cli-logo.png" />
</p>

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

## Documentation


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
