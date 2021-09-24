# Orbiter\Satellite

```lang-none


                       🌐
                     🛰️
                   ·          
                · 
     🛰️🛰️️   ·
    🛰️🌐🛰️   ·  ·  · 🛰️🌐
     🛰️🛰️️

 
```

Easy to use Event & Middleware Framework, powered by popular micro-libraries and based on [PSRs](https://www.php-fig.org/psr/).

>
> todo: current `/docs/` are outdated
>

- [Setup](#setup)
    - [Config](#config)
- [Implemented PSRs](#psrs)
- [Used Packages](#used-packages)
- Features
    - [Events](docs/feature-events.md)
    - [Routing](docs/feature-routing.md)
        - [Middleware](docs/feature-middleware.md)
    - [Console](docs/feature-console.md)
    - [DI](docs/feature-di.md)
    - [Annotations](docs/#setup-annotations)
- Extend
    - replace [System Event](docs/satellite-events.md) handlers
    - extend/replace the core [Event Dispatcher or Invoker](docs/#extend-event-dispatcher-or-invoker)
- [Download Build](#download-build)
- [License](#license)

Supports PHP 7.4.1+ and 8+ and [composer](https://getcomposer.org/)

## TL;DR

Quick-Jump into develop:

```bash
composer create-project orbiter/satellite-app ./satellite

cd ./satellite

# create `.env` file, should add for dev: `env=dev`
touch .env 

# start PHP Dev Server
cd web && php -S localhost:3333 ./index.php display_errors=0

# or point the Apache Root to `/web/`
# or point the NGINX entry to `/web/index.php`
# or use Docker: `docker-compose up`
```

Open your browser on: http://localhost:3333

Look into files:

- `launch.php` - setup, DI, Annotations, dispatch `SatelliteApp` event
- `/config/*.php`, [app config and wiring](#config)

## Setup

Install app skeleten and dependencies with composer in folder `satellite`:

```bash
composer create-project orbiter/satellite-app satellite

# composer with docker on windows:
docker run -it --rm -v %cd%/satellite:/app composer create-project orbiter/satellite-app .
# composer with docker on unix:
docker run -it --rm -v `pwd`/satellite:/app composer create-project orbiter/satellite-app .

cd ./satellite
```

### Linux, PHP Dev Server

Setup correct user rights, then start and open http://localhost:3333

```bash 
chmod +x start.sh

./start.sh
```

### PHP Command-Line

Execute defined commands.

```bash 
php cli <command> <..attr> <..b>
```

### Docker, docker-compose

Includes configurable PHP Dockerfile with:

- PHP 8.x
    - with FPM and a few extensions
    - reusing FPM for a clean CLI worker image
- OPCache
- PostgreSQL Client libs (using `pdo`)
- NGINX base image for local routing
- customize in [Dockerfile](Dockerfile)
- a more "production" ready image, preconfigured for building [in CI](.github/workflows/blank.yml) with [docker-compose--prod.yml](docker-compose--prod.yml)

For docker image configs see files in `_docker` and `_nginx`.

Start containers specified in `docker-compose.yml`, then open: http://localhost:3333

```bash
docker-compose up

# open shell in app container
docker-compose exec app sh

# run extra composer container on windows:
docker run -it --rm -v %cd%:/app composer dumpautoload
# run extra composer container on unix:
docker run -it --rm -v `pwd`:/app composer dumpautoload

# run tests with temporary `app` container:
docker-compose run -T --rm app sh -c "cd /var/www/html && ./vendor/bin/phpunit --testdox tests"

# running tests with a temporary `phpunit` in a prebuild container:
docker run -i --rm bemiteu/satellite-app:master sh -c "cd /var/www && wget -O phpunit https://phar.phpunit.de/phpunit-9.phar && chmod +x phpunit && cd html && /var/www/phpunit --testdox tests"
```

### Web-Server

On a web-server the `web/index.php` file serves as public entry point.

- Apache: point server/vhost root to `/web` and use the included `.htaccess`
- nginx example directive:

    ```.conf
    location / {
        # DOCROOT must contain absolute path to `web`, in this example 
        root {DOCROOT};
        # try to serve file directly, fallback to index.php
        try_files $uri /index.php$is_args$args;
    }
    ```

### Config

Use `.env` to add configuration, see [Features](docs) for how to configure/setup different logic parts.

Default's config includes:

- env var `env` if in production or not in production
    - with value `prod` it is assumed in the App (not the framework) that it is in production
    - use `$_ENV['env'] === 'prod'` to check for production
- `/config/config.php` - meta config
- `/config/dependencies.php` - definitions for PHP-DI
- `/config/events.php` - define app components flow
- `/config/pipeline.php` - setup PSR middlewares and pipeline

## PSRs

This app serves as mini-framework, with PSR powered libraries, ready-to-use Annotations and not much more.

It is build upon [PSRs](https://www.php-fig.org/psr/) and popular, specialized packages implementing them or other great stuff.

- **PSR-3** - Logger, use: [monolog](https://github.com/Seldaek/monolog)
- **PSR-4** - autoloading classes and forget require
    - handled by composer, more in [composer docs.](https://getcomposer.org/doc/01-basic-usage.md#autoloading)
- **PSR-1,12** - Code Style Guides
    - but we break the brackets location rule, same-line instead of next-list for opening `{`
- **PSR-6** - Cache, use a [PHP-Cache Bridge](http://www.php-cache.com/en/latest/)
    - *currently* integrates Doctrine cache for some features
- **PSR-7** - HTTP Message
    - request and response data definitions
- **PSR-11** - Container for InterOp
    - full support for any compliant container
    - use in console command handlers
    - use in event handlers
    - use in middleware handlers
- **PSR-14** - Event Dispatcher and Listener
    - as the core of how things are put together
- **PSR-15** - HTTP Handlers
    - handling requests with executing the middleware pipe
- **PSR-16** - Simple Cache, use e.g. [Doctrine SimpleCache adapter](https://github.com/Roave/DoctrineSimpleCache)
- **PSR-17** - HTTP Factories are used but not all features are wired (partly)
    - create context about request
    - useful for uploads and streams
- **PSR-18** - HTTP Client *(todo)*

## Used Packages

Orbiter has optimized packages, each encapsulates one system part or extension.

Most packages can be replaced with any PSR implementation or another framework or nothing at all.

- `orbiter/satellite`
    - the core + event handler
    - implements **PSR-14** Event Dispatcher and Listener
    - with invoker to execute anything, **PSR-11** compatible
    - see [package repository](https://github.com/bemit/satellite)
- `orbiter/satellite-console`
    - console execution
    - console command annotations
    - uses [getopt-php](https://github.com/getopt-php/getopt-php)
    - see [Console](docs/feature-console.md)
- `orbiter/satellite-response`
    - middleware pipe execution
    - implements **PSR-15** through `equip/dispatch`, **PSR-11** compliant
    - implements **PSR-7,17** through `nyholm/psr7` and `nyholm/psr7-server`
    - with simple emitter by `narrowspark/http-emitter`
    - see [Middleware](docs/feature-middleware.md)
- `orbiter/satellite-route`
    - routes by annotations
    - uses [nikic/fast-route](https://github.com/nikic/FastRoute) as router
    - made for PSR middleware usage, but not limited
    - see [Routing](docs/feature-routing.md)
- `orbiter/annotations-util`
    - annotations by `doctrine/annotations` with cached reflections
    - get classes, methods and properties which are annotated
    - see [AnnotationsUtil](https://github.com/bemit/orbiter-annotations-util)
- `orbiter/satellite-whoops`
    - Whoops error display for CLI and Routes
    - only when `$_ENV['env']` not `prod` (configurable in `launch.php`)
- Dependency Injection
    - implements **PSR-11** through [php-di](http://php-di.org)
    - see [DI](docs/feature-di.md)

A lot of work is done by PSR-15 HTTP Middlewares provided by [github.com/middlewares](https://github.com/middlewares), find more [awesome middlewares](https://github.com/middlewares/awesome-psr15-middlewares).

## Features

Satellite integrates into a micro framework to rapidly build PHP server apps, mostly - but not limited - for non-human clients.

See [docs](docs) for further information.

## Download Build

There is no downloadable version - see [Setup](#setup) on how to install with composer.

We use composer as package manager, like in any modern PHP project.

Feel free to reach out for a [training request](https://mlbr.xyz).

## License

This project is free software distributed under the [**MIT License**](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository.

***

Maintained by [Michael Becker](https://mlbr.xyz)
