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

- [Setup](#setup)
    - [Config](#config)
- [Implemented PSRs](#psrs)
- [Packages](#packages)
- [Download Build](#download-build)
- [License](#license)

Supports PHP 8.1+ and [composer](https://getcomposer.org/)

## TL;DR

Quick-Jump into develop:

```bash
composer create-project orbiter/satellite-app ./satellite

cd ./satellite

# create `.env` file, 
# add for local-dev: `env=local`
touch .env 

# start PHP Dev Server
cd web && php -S localhost:3333 ./index.php display_errors=0

# or point the Apache Root to `/web/`
# or point the NGINX entry to `/web/index.php`
# or use Docker: `docker-compose up`
```

Open your browser on: http://localhost:3333

Look into files:

- [`config`](./config) folder with [app config and wiring](#config)
- [`assemble.php`](./assemble.php) include composer autoload, setup DI, Annotations, gather configurations and create the system modules from config
- [`launch.php`](./launch.php) runs `assemble()` and dispatches the `SatelliteApp` event
- [`app`](./app) folder with a basic commands and route handler structure

## Setup

Install app skeleten and dependencies with composer in folder `satellite`:

```bash
# with composer installed on machine:
composer create-project orbiter/satellite-app satellite

# with composer and docker on windows:
docker run -it --rm -v %cd%/satellite:/app composer create-project orbiter/satellite-app .
docker run -it --rm -v %cd%/satellite:/app composer create-project --stability=dev orbiter/satellite-app:dev-master .

# with composer and docker on unix:
docker run -it --rm -v `pwd`/satellite:/app composer create-project orbiter/satellite-app .
docker run -it --rm -v `pwd`/satellite:/app composer create-project --stability=dev orbiter/satellite-app:dev-master .

# go into project folder:
cd ./satellite
```

Run with:

- [Linux, PHP Dev Server](#linux-php-dev-server)
- [PHP Command-Line](#php-command-line)
- [Docker, docker-compose](#docker-docker-compose)
- [Web-Server](#web-server)

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

- PHP 8.1
    - with FPM and a few extensions
    - reusing FPM for a clean CLI worker image
- OPCache
- PostgreSQL Client libs (using `pdo`)
- NGINX base image for local routing
- customize in [Dockerfile](Dockerfile)
- a more "production" ready image, preconfigured for building [in CI](.github/workflows/blank.yml) with [docker-compose--prod.yml](docker-compose--prod.yml)

For docker image configs see files in [`_docker`](./_docker) and [`_nginx`](./_nginx).

Start containers specified in [`docker-compose.yml`](./docker-compose.yml), then open: [http://localhost:3333](http://localhost:3333)

```bash
docker-compose up

# open shell in app container
docker-compose exec app sh

# run command in temporary worker container
docker-compose run --rm worker php cli demo

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

**Apache:** point server/vhost root to `/web` and use the included `.htaccess`

**NGINX**: example files in [_nginx](./_nginx).

## Config

Use e.g. `.env` to add configuration.

Default's config includes:

- env var `env` if in production or not in production
    - with value `prod` it is assumed in the App (not the framework) that it is in production
    - use `$_ENV['env'] === 'prod'` to check for production
    - for dev-error pages: add var `dev.editor` with one value of `PrettyPageHandler::EDITOR_*` to link `whoops` "open file" with IDE
- `/config/config.php` - main config
    - configures which other config files are included
    - aggregates and caches the config for production usage
- `/config/dependencies.php` - definitions for PHP-DI
- `/config/events.php` - define app components flow
- `/config/pipeline.php` - setup PSR middlewares and pipeline

## PSRs

This app serves as mini-framework, with PSR powered libraries, ready-to-use Annotations and not much more.

It is build upon [PSRs](https://www.php-fig.org/psr/) and popular, specialized packages implementing them or other great stuff.

- **PSR-3** - Logger
    - 📦 `monolog/monolog`
    - [more implementations](https://packagist.org/providers/psr/log-implementation)
- **PSR-4** - autoload classes and forget `require_once`
    - handled by composer, more in [composer docs.](https://getcomposer.org/doc/01-basic-usage.md#autoloading)
- **PSR-1,12** - Code Style Guides
    - except brackets location rule: same-line instead of next-line for opening `{`
- **PSR-6** - Cache
    - 📦 `cache/filesystem-adapter`
    - includes [`FilesystemCachePoolNormalized`](./app/Lib/FilesystemCachePoolNormalized.php) for `Doctrine\Common\Annotations\PsrCachedReader` compatibility
    - [more implementations](https://packagist.org/providers/psr/cache-implementation)
- **PSR-7** - HTTP Message
    - 📦 `nyholm\psr7`
    - request and response data definitions
    - used further by `PSR-15`, `PSR-17` and `PSR-18`
- **PSR-11** - Container for InterOp
    - 📦 `php-di/php-di`
    - service container for ease and modularity
    - dependency injection with `@Annotations`, `@var` PHPDoc and Reflection support
- **PSR-14** - Event Dispatcher and Listener
    - 📦 `orbiter/satellite`
    - as the core of how things are put together
- **PSR-15** - HTTP Handlers
    - 📦 `orbiter/satellite-response`
    - handle route requests with a powerful middleware pipeline
- **PSR-16** - Simple Cache
    - 📦 `cache/filesystem-adapter`
    - [more implementations](https://packagist.org/providers/psr/simple-cache-implementation)
- **PSR-17** - HTTP Factories
    - 📦 `nyholm\psr7`
    - context about request
    - for request & response initiations
- **PSR-18** - HTTP Client
    - 📦 `guzzlehttp/guzzle`
    - send requests to other APIs
    - [more implementations](https://packagist.org/providers/psr/http-client-implementation)

> 📦 = included in `satellite-app` template

## Packages

- `orbiter/satellite` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite)
    - implements **PSR-14** Event Dispatcher and Listener
    - with invoker to execute anything, **PSR-11** compatible
    - optional event-handler based profiling
    - see [package repository](https://github.com/bemit/satellite)
- `orbiter/satellite-console` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-console/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-console)
    - console execution
    - console command annotations
    - uses [getopt-php](https://github.com/getopt-php/getopt-php)
    - see [package repository](https://github.com/bemit/satellite-console)
- `orbiter/satellite-response` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-response/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-response)
    - middleware pipe execution
    - implements **PSR-15** through `equip/dispatch`, **PSR-11** compliant
    - implements **PSR-7,17** through `nyholm/psr7` and `nyholm/psr7-server`
    - with simple emitter by `narrowspark/http-emitter`
    - see [package repository](https://github.com/bemit/satellite-response)
- `orbiter/satellite-route` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-route/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-route)
    - routes by annotations
    - uses [nikic/fast-route](https://github.com/nikic/FastRoute) as router
    - made for PSR middleware usage, but not limited
    - see [package repository](https://github.com/bemit/satellite-route)
- `orbiter/annotations-util` [![Latest Stable Version](https://poser.pugx.org/orbiter/annotations-util/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/annotations-util)
    - annotations by `doctrine/annotations` with cached reflections
    - get classes, methods and properties which are annotated
    - see [AnnotationsUtil](https://github.com/bemit/orbiter-annotations-util)
- `orbiter/satellite-whoops` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-whoops/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-whoops)
    - Whoops error display for CLI and Routes
    - only when `$_ENV['env']` not `prod` (configurable in [`assemble.php`](./assemble.php))
- `orbiter/satellite-config` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-config/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-config)
    - simple config aggregator with caching
    - see [package repository](https://github.com/bemit/satellite-config)
- `orbiter/satellite-launch` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-launch/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-launch)
    - `SatelliteApp` event data objects
    - see [package repository](https://github.com/bemit/satellite-launch)
- `orbiter/satellite-system` [![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-system/version.svg?style=flat-square)](https://packagist.org/packages/orbiter/satellite-system)
    - system setup and core wire-up, e.g. from `$config` to a cached PSR container
    - see [package repository](https://github.com/bemit/satellite-system)

A lot of work for APIs is done by PSR-15 HTTP Middleware, find more [awesome middlewares](https://github.com/middlewares/awesome-psr15-middlewares).

## Download Build

There is no downloadable version - see [Setup](#setup) on how to install with composer.

We use composer as package manager, like in any modern PHP project.

Feel free to reach out for a [training request](https://bemit.codes).

## License

This project is free software distributed under the [**MIT License**](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository.

***

Maintained by [Michael Becker](https://i-am-digital.eu)
