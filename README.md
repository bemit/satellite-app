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

Requires PHP 7.3+ and [composer](https://getcomposer.org/)

## TL;DR

Quick-Jump into develop:

```bash
composer create-project orbiter/satellite-app ./satellite

cd ./satellite

php -S localhost:3333 ./server.php display_errors=0 # start PHP Dev Server
# or point the Apache Root to `/web/`
# or point the NGINX entry to `/web/index.php`
# or use Docker: `docker-compose up`
```

Open your browser on: http://localhost:3333

Look into files:

- `_commands.php` - define console commands, see [getopt-php](https://github.com/getopt-php/getopt-php) on command details
- `_routes.php` - define routes, see [nikic/fast-route](https://github.com/nikic/FastRoute) for info about paths syntax, see [routing](docs/feature-routing.md) for how to register routes in Satellite
- `_launch.php` - add events to the flow and modify middlewares

Everything else is up to you!

## Setup

Install app skeleten and dependencies with composer:

```bash
composer create-project orbiter/satellite-app satellite

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

- PHP 7.3
- Apache
- OPCache
- MySQL Client libs (pdo, pdo_mysql, mysqli)
- Apache Mods: rewrite, deflate, brotli
- customize in [Dockerfile](Dockerfile)

Configs are system originals, see files `docker-*` amd their respective docs.

Start containers specified in `docker-compose.yml`, then open: http://localhost:3333

```bash
docker-compose up

# open shell in app container
docker-compose exec app sh
# use composer integrated in image
composer require monolog/monolog
```

### Web-Server

On a web-server the `web/index.php` file serves as public entry point.

- Apache: point server/vhost root to `/web` and use the included `.htaccess`
- nginx directive:

```.conf
location / {
    # DOCROOT must contain absolute path to `web`, in this example 
    root {DOCROOT};
    # try to serve file directly, fallback to index.php
    try_files $uri /index.php$is_args$args;
}
```

> The CLI could be used in e.g. crons on the production server, in CI during build - but **don't use** `server.php` as production entry point.

### Config

Use `.env` to add configuration, see [Features](docs) for how to configure/setup different logic parts.

Default's config includes:

- `env` if in production or not in production
    - with value `prod` it is assumed in the App (not the framework) that it is in production
    - use `getenv('env') === 'prod'` to check for production

## PSRs

This app serves as mini-framework, with PSR powered libraries and not much more.

It is build upon [PSRs](https://www.php-fig.org/psr/) and popular, specialized packages implementing them or other great stuff.

- **PSR-3** - Logger *(todo)*
- **PSR-4** - autoloading classes and forget require
    - handled by composer, more in [composer docs.](https://getcomposer.org/doc/01-basic-usage.md#autoloading)
- **PSR-1,12** - Code Style Guides
    - but we break the brackets location rule, same-line instead of next-list for opening `{`
- **PSR-6** - Cache *(todo)*
- **PSR-7** - HTTP Message
    - request and response data definitions
- **PSR-11** - Container for InterOp
    - full support for any compliant container
- **PSR-14** - apt ges and Listeners
    - as the core of how things are put together
- **PSR-15** - HTTP Handlers
    - handling requests with executing the middleware pipe
- **PSR-17** - HTTP Factories are used but not all features are wired (partly)
    - create context about request
    - useful for uploads and streams
- **PSR-18** - HTTP Client *(todo)*

## Used Packages

For Satellite are optimized packages that each on is own encapsulates one system part.

Only the event core is needed, most packages can be replaced with any PSR implementation or another framework or nothing at all.

To integrate fully into Satellite, replacements of core events must implement handling the dispatched event instead of the package that would normally handle the execution and encapsulates the dependencies.

- `orbiter/satellite`
    - the core + event handler
    - implements **PSR-14** Event Dispatcher and Listener
    - with invoker to execute anything, **PSR-11** compatible
    - with singleton `Satellite\Event` to register and dispatch events
    - origin of [SystemLaunchEvent](docs/satellite-events.md#systemlaunchevent)
    - see [Events](docs/feature-events.md)
- `orbiter/satellite-console`
    - console execution
    - origin of [ConsoleEvent](docs/satellite-events.md#consoleevent)
    - uses [getopt-php](https://github.com/getopt-php/getopt-php)
    - see [Console](docs/feature-console.md)
- `orbiter/satellite-response`
    - middleware pipe execution
    - implements **PSR-15** through `equip/dispatch`, **PSR-11** compliant
    - with simple emitter by `narrowspark/http-emitter`
    - see [Middleware](docs/feature-middleware.md) 
- `orbiter/satellite-route`
    - routing execution
    - origin of [RouteEvent](docs/satellite-events.md#routeevent)
    - uses [nikic/fast-route](https://github.com/nikic/FastRoute) as router
    - special generation syntax for routes
    - implements **PSR-7,17** through `nyholm/psr7` and `nyholm/psr7-server` 
    - see [Routing](docs/feature-routing.md)
- `orbiter/satellite-di`
    - dependency injection
    - implements **PSR-11** through [php-di](http://php-di.org)
    - see [DI](docs/feature-di.md)
- `orbiter/annotations-util`
    - annotations by `doctrine/annotations` with cached reflections
    - see [AnnotationsUtil](https://github.com/bemit/orbiter-annotations-util)
- `orbiter/satellite-whoops`
    - Whoops error display for CLI and Routes
    - only when `getenv('env')` not `prod` (configurable in `launch.php`)
    
A lot of work is done by Utils provided by [GitHub Middlewares](https://github.com/middlewares), find more [awesome middlewares](https://github.com/middlewares/awesome-psr15-middlewares).

## Features

Satellite integrates into a micro framework to rapidly build PHP server apps, mostly - but not limited - for non-human clients.

See [docs](docs) for further information.

## Download Build

There is no downloadable version - see [Setup](#setup) on how to install with composer.

We use composer as package manager, like in any modern PHP project.

Feel free to reach out for a [training request](https://mlbr.xyz).

## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

***

Maintained by [Michael Becker](https://mlbr.xyz)
