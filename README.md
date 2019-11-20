# Orbiter\Satellite

Easy to use Event & Middleware Mini-Framework, powered by popular projects and PSRs.

- [Config](#config)
- [Setup](#setup)
- [Used Packages + PSRs](#used-packages)
- [Features](#features)
    - [Events](#feature-events)
    - [Routing](#feature-routing)
        - [Middleware](#feature-middleware)
    - [Console](#feature-console)
- [Download Build](#download-build)
- [License](#license)

Requires PHP 7.2+ and [composer](https://getcomposer.org/)

___

## TL;DR

For a Quick-Jump into develop:

```bash
composer create-project orbiter/satellite-app ./satellite

cd ./satellite

php -S localhost:3333 ./server.php display_errors=0
```

Open your browser on: http://localhost:3333

> Docker Support, just: `docker-compose up` and go to `http://localhost:3333`

Look into files:

- `_commands.php` - define console commands, see [getopt-php](https://github.com/getopt-php/getopt-php) on command details
- `_routes.php` - define routes, see [nikic/fast-route](https://github.com/nikic/FastRoute) for info about paths and callables
- `_launch.php` - add events to the flow and add modify middlewares

Everything else is up to you!

___

## Config

Use `.env` to add configuration, see [Features](#features) for how to configure/setup different logic parts.

## Setup

```bash

composer create-project orbiter/satellite-app ./satellite

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

Configs are system defaults, see files `docker-*`

Start containers specified in `docker-compose.yml`, then open: http://localhost:3333

```bash
docker-compose up
```

### Web-Server

On a web-server the `web/index.php` file serves as public entry point.

The cli could be used for e.g. crons.

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

## Used Packages

This app serves as mini-framework and exemplary of how to put together a custom Orbiter\Satellite stack.

It is build upon [PSRs](https://www.php-fig.org/psr/).

And popular (specialized) packages implementing them or other great stuff.

- PSR-4 - autoloading classes and forget require (handled by `composer`)
- PSR-1,12 - Code Style Guides, but we break the brackets location rule, same-line instead of next-list for opening `{`
- PSR-7 - HTTP Message, request and response data definitions
- PSR-11 - Container for InterOp (todo)
- PSR-14 - Events and Listeners as the core of how things are put together
- PSR-15 - HTTP Handlers, handling requests
- PSR-17 - HTTP Factories are used but not all features are wired (partly)
- PSR-18 - HTTP Client (todo)

In Orbiter those packages are bundling core logic:

- `orbiter/satellite`
    - implements PSR-14 Event Dispatcher and Listener
    - supplies singleton `Satellite\Event` to register and dispatch events
    - includes the system launch event and initial dispatcher
    - see [Events](#feature-events)
- `orbiter/satellite-console`
    - supplies the console execution to `satellite`
    - uses [getopt-php](https://github.com/getopt-php/getopt-php)
    - see [Console](#feature-console)
- `orbiter/satellite-response`
    - supplies general Middleware execution to `satellite`
    - supplies PSR-15 middlewares, dispatcher
    - implements PSR-15 through `equip/dispatch`, PSR-11 compatible
    - simple emitter by `narrowspark/http-emitter`
    - see [Middleware](#feature-middleware) 
- `orbiter/satellite-route`
    - supplies general routing execution to `satellite`
    - uses [nikic/fast-route](https://github.com/nikic/FastRoute) as router
    - supplies an array definition syntax for routes
    - implements PSR-7,17 through `nyholm/psr7` and `nyholm/psr7-server` 
    - see [Routing](#feature-routing)
- `orbiter/satellite-whoops`
    - add the Whoops error display for CLI and Routes
    - only when `getenv('env')` not `prod` (configurable in `launch.php`)
    
A lot of work is done by Utils provided by [GitHub Middlewares](https://github.com/middlewares), find more [awesome middlewares](https://github.com/middlewares/awesome-psr15-middlewares).

## Features

Satellite integrates into a micro framework to rapidly build PHP server apps, mostly - but not limited - for non-human clients.

### Feature Events

At the core is an event system that controls the flow of satellite and lets you extend it easily.

Building event pipes and connecting your contexts through domain-oriented events makes it easy to build scalable and useful business software.

Any event exists of an `object`, that may have properties and methods, the object gets dispatched and passed along all listeners registered for that event.

Event listeners are registered with the class signature of the event and one callable (or InterOp resolvable).

#### Event Store

`Event` provides a singleton store to the dispatcher and listener for direct use across the projest.

```php
<?php
use Satellite\Event;

Event::on(string event, callable listener);
Event::dispatch(string event);
Event::dispatcher(): EventDispatcherInterface;
```

#### Event Listener

As example we use the only included event `Satellite\SystemLaunchEvent`, this get's triggered when you start satellite, no matter from where.

```php
<?php
use Satellite\SystemLaunchEvent;
use Satellite\Event;

// Register a listener to SystemLaunch
// this will execute the callable when the event `SystemLaunchEvent` is dispatched
Event::on(SystemLaunchEvent::class, static function(SystemLaunchEvent $launch) {
    // the callable receives the event it is called for
    
    error_log('Hi! You will see me in the console with e.g. php cli version');
    // here you can change or use the event payload
    $launch->cli = !!$launch->cli;// todo: add better example

    // and must return it
    return $launch;
});
```

#### Event Dispatching

Dispatching an event is easy, just instantiate the event you need, add some data and dispatch it!

```php
<?php
use Satellite\SystemLaunchEvent;
use Satellite\Event;

// these 4 lines are the actual code of how satellite is launched!
$launch = new SystemLaunchEvent();
$launch->cli = PHP_SAPI === 'cli';

Event::dispatch($launch);
```

#### Event Definition

Define an event with any class, it is preferred that it supports loss-less serialization, but that's not required.

```php
<?php
namespace Satellite;

use Psr\EventDispatcher\StoppableEventInterface;

use Satellite\Event\StoppableEvent;

class SystemLaunchEvent implements StoppableEventInterface {
    use StoppableEvent;

    /**
     * @var bool
     */
    public $cli = false;
}
```

This event also implement the optional `StoppableEvent`, in `Satellite\Event` a trait is included for easy usage:

```php
<?php

// inside class (see above)
use StoppableEvent;


// inside a listener
$this->stopPropagation(); // stop the event execution after this handler
// the trait is just stoppable, but not resumable

// method of the interface, returns a boolean
$this->isPropagationStopped();
```

#### Inheritance of Events
 
If one event inherits another:

1. the registered listeners for the object are added
2. the parent classes of the object are fetched
3. for each parent class, the events are added
4. all events are executed first-in first-out

If you want to end after yours, the event must implement a StoppableEvent

#### Event Dispatcher Instance 

You can get the actual dispatcher instance from the `Event` singleton.

```php
<?php
use Satellite\Event;

$dispatcher = Event::dispatcher();
$dispatcher->dispatch(<your-class>);
```

### Feature Routing

In routing a request will be transformed to a response, the request is typically an url and the response e.g. HTML or JSON.

The integrated routing library is build upon [nikic/fast-route](https://github.com/nikic/FastRoute) and provides an describing interface that makes it easier to conditionally addition of routes - instead of the only callable way.

This describing interface leverages the vocabulary from nikic.

Any route must receive a callable that is a PSR-15 valid handler, when [DI](#feature-di) is added it further on get's dependencies injected.

The `Router` stores all routes during boot and launch and registers them to FastRoute. Later on int the [Middlewares](#feature-middleware) the router matching the routes and the output is displayed.

```php
<?php

use Satellite\KernelRoute\Router;

// FastRoute doesn't have `$id`, this is the main difference in the properties

// Add an single route with all information needed
Router::addRoute(string $id, string $method, string $route, callable $handler);

// Adds a group of routes, all get the same prefix before their route
Router::addGroup(string $id, string $prefix, array $routes);

// these must be used inside of `addGroup`
Router::get(string $route, callable $handler);
Router::post(string $route, callable $handler);
Router::put(string $route, callable $handler);
Router::delete(string $route, callable $handler);
```

```php
<?php

use Satellite\KernelRoute\Router;
use Satellite\Response\Respond;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\RequestHandlerInterface;

Router::addRoute(
    'home',// id
    'GET',// method: GET, POST, PUT, DELETE
    '/',// path like FastRoute supports // todo: add link
    // a PSR compatible handler
    static function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
        // Output like `RequestHandler` supports // todo: add link
        return '<!doctype HTML><html><h2 style="font-family: sans-serif">Satellite 🛰️</h2></html>';

        // or e.g se the `Respond::json` utility to build some api output
        return Respond::json(['Demo', 'Data', 'As', 'JSON'], $request, $handler);

        // or do something else
        // ...

        // execute lower, may be returned directly
        $response = $handler->handle($request);

        // or change something and return
        $response = $response->withHeader('Content-Type', 'application/json');

        return $response;
    }
);

// Add a group, use the short `Router::get` syntac
Router::addGroup(
    'api', '/api',
    [
        'auth' => Router::post('/auth', static function() {
    
        }),
        'newsletter.status' => Router::get(
            '/newsletter/status/{email}',// path with parameters, complex situations are possible 
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            
            // path parameters are added to the request attributes
            $email = $request->getAttribute('email');

            // get status and generate response body..

            return $handler->handle($request);
        }),
    ]
);

``` 

### Feature DI

> // todo

### Feature Middleware

> // todo

### Feature Console

> // todo

## Download Build

> // todo

## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

***

Maintained by [Michael Becker](https://mlbr.xyz)
