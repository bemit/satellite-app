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
- [Used Packages + PSRs](#used-packages)
- [Features](#features)
    - [Events](#feature-events)
    - [Routing](#feature-routing)
        - [Middleware](#feature-middleware)
    - [Console](#feature-console)
    - [DI](#feature-di)
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
- `_routes.php` - define routes, see [nikic/fast-route](https://github.com/nikic/FastRoute) for info about paths syntax, see [routing](#feature-routing) for how to register routes in Satellite
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

Use `.env` to add configuration, see [Features](#features) for how to configure/setup different logic parts.

## Used Packages

This app serves as mini-framework and exemplary of how to put together a custom Orbiter\Satellite stack.

It is build upon [PSRs](https://www.php-fig.org/psr/) and popular (specialized) packages implementing them or other great stuff.

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
- **PSR-14** - Events and Listeners
    - as the core of how things are put together
- **PSR-15** - HTTP Handlers
    - handling requests with executing the middleware pipe
- **PSR-17** - HTTP Factories are used but not all features are wired (partly)
    - create context about request
    - useful for uploads and streams
- **PSR-18** - HTTP Client *(todo)*

In Orbiter those packages are bundling core logic:

- `orbiter/satellite`
    - the core + event handler
    - implements **PSR-14** Event Dispatcher and Listener
    - with invoker to execute anything, **PSR-11** compatible
    - with singleton `Satellite\Event` to register and dispatch events
    - origin of `SystemLaunchEvent`
    - see [Events](#feature-events)
- `orbiter/satellite-console`
    - console execution
    - origin of `ConsoleEvent`
    - uses [getopt-php](https://github.com/getopt-php/getopt-php)
    - see [Console](#feature-console)
- `orbiter/satellite-response`
    - middleware pipe execution
    - implements **PSR-15** through `equip/dispatch`, **PSR-11** compliant
    - with simple emitter by `narrowspark/http-emitter`
    - see [Middleware](#feature-middleware) 
- `orbiter/satellite-route`
    - routing execution
    - uses [nikic/fast-route](https://github.com/nikic/FastRoute) as router
    - special generation syntax for routes
    - implements **PSR-7,17** through `nyholm/psr7` and `nyholm/psr7-server` 
    - see [Routing](#feature-routing)
- `orbiter/satellite-di`
    - dependency injection
    - implements **PSR-11** through [php-di](http://php-di.org)
    - see [DI](#feature-di)
- `orbiter/satellite-whoops`
    - Whoops error display for CLI and Routes
    - only when `getenv('env')` not `prod` (configurable in `launch.php`)
    
A lot of work is done by Utils provided by [GitHub Middlewares](https://github.com/middlewares), find more [awesome middlewares](https://github.com/middlewares/awesome-psr15-middlewares).

## Features

Satellite integrates into a micro framework to rapidly build PHP server apps, mostly - but not limited - for non-human clients.

### Feature Events

At the core is an event system that controls the flow of Satellite and lets you extend it easily.

Building event pipes and connecting your contexts through domain-oriented events makes it easy to build scalable and useful business software.

Any event consists of an `object`, that may have properties and methods, the object gets dispatched and passed along all listeners registered for that event.

Event listeners are registered with the class signature of the event and one callable (or InterOp resolvable).

#### Event Store

`Event` provides a singleton store to the dispatcher and listener for direct use across the project.

```php
<?php
use Satellite\Event;

Event::on(string $event, callable $listener);
Event::dispatch(string $event);
Event::dispatcher(): EventDispatcherInterface;
```

#### Event Listener

As example we use the event `Satellite\SystemLaunchEvent`, this get's triggered when you `launch()` Satellite, no matter from where.

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

##### Event Listener Delegation

With `Delegate` it is possible to create an e.g. handler out of data and then letting `Event` handle the real execution.

> The `handler` in an delegation is free to return either the event or nothing.
>
> If nothing the event of `Delegate` is pushed to the next handlers.

```php
<?php
use Satellite\Event;
use Satellite\KernelConsole\ConsoleEvent;

// The ConsoleEvent is dispatched somewhere, but not executed. This is done by simply delegating the handler execution also to the event system.
// this forces that the handler is executed in the context of the provided event - which may derive from the one received
Event::on(ConsoleEvent::class, static function($evt) {
    return new Event\Delegate($evt->handler, $evt);
});
```

> `Delegate` is a powerful function that should not be used too often, as it can easily lead to chaotic domain contexts.
>
> Good use-cases are for example:
>
> 1. Conditionally create/extract the handler that is responsible for the actual handling
> 2. **Generic** Decorator or Transformer functions, e.g. formatting and sanitizing of values that are already provided

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

This event also implements the optional `StoppableEvent`, in `Satellite\Event` a trait is included for easy usage:

```php
<?php

// inside class (see above)
use Satellite\Event\StoppableEvent;


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

Any route must receive a callable that is a **PSR-15** valid handler.

When [DI](#feature-di) is added, the handler can be any valid callable/container-resolvable and the DI creates the objects with injected dependencies.

The `KernelRoute\Router` stores all routes during boot and launch and registers them to FastRoute. Later on in the [Middlewares](#feature-middleware) the router matching the routes and the output is displayed.

```php
<?php

use Satellite\KernelRoute\Router;

// FastRoute doesn't have `$id`, this is the main difference in the properties

// Add an single route with all information needed
Router::addRoute(string $id, string $method, string $route, callable $handler);

// Adds a group of routes, all get the same prefix before their route
Router::addGroup(string $id, string $prefix, array $routes);

//
// these must be used inside of `addGroup`
Router::get(string $route, callable $handler);
Router::post(string $route, callable $handler);
Router::put(string $route, callable $handler);
Router::delete(string $route, callable $handler);

Router::group(string $prefix, array $routes);// for nested groups
```

```php
<?php

use Satellite\KernelRoute\Router;
use Satellite\Response\Respond;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

Router::addRoute(
    'home',// id
    'GET',// method: GET, POST, PUT, DELETE
    '/',// path like FastRoute supports, https://github.com/nikic/FastRoute#defining-routes
    // a PSR compatible handler
    static function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
       
        // when using: middlewares/request-handler

        // echo output will get captured and written into body
        echo '<!doctype HTML><html><h2 style="font-family: sans-serif">Satellite 🛰️</h2></html>';

        // return a string and it gets written into body
        return '<!doctype HTML><html><h2 style="font-family: sans-serif">Satellite 🛰️</h2></html>';
        
        // or in general:

        // .. do stuff and add to arguments

        //  execute lower
        $response = $handler->handle($request);

        // .. add content from this middleware to body
        $body = $response->getBody();
        $str = 'Hey!';
        if($str !== '' && $body->isWritable()) {
            $body->write($str);
        }
        
        // maybe add headers
        $response = $response->withHeader('Content-Type', 'application/json');
    
        // and return response
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

        // nested groups with just `group`
        'v1' => Router::group('/v1', [])
    ]
);
```

##### Cached Router

Enable the cached router before the launch event, `string` with path to file or `null`. Default is off (`null`).

```php
<?php
use Satellite\KernelRoute\Router;

Router::setCache(getenv('env') === 'prod' ? __DIR__ . '/tmp/route.cache' : null);
```

#### Feature Middleware

Routing requests to execution is handled by a **PSR-15** compatible Middleware pipe. Use [any middleware](https://github.com/middlewares/awesome-psr15-middlewares) to easily add complex logic.

Satellite provides a pipe that makes it easy to add and configure middlewares.

The pipe must be created inside a `RouteEvent` handler to consume the `request` and `router` created by `orbiter/satellite-route` - or use your preferred **PSR-7,17** libs and **PSR-15** router-middleware.

```php
<?php

use Satellite\Event;

use Satellite\KernelRoute\RouteEvent;
use Satellite\Response\RespondPipe;

Event::on(RouteEvent::class, static function(RouteEvent $resp) {
    $pipe = new RespondPipe();

    // ... add here e.g. payload extractors, access checks

    // add the automatically generated router middleware, this middleware contains all routes registered
    $pipe->with($resp->router);// any compatible MiddlewareInterface router is possible

    // add the request handler middleware after the router, this executes the matched handler from router
    $pipe->with(new Middlewares\RequestHandler());
    
    // ... add here e.g. middleware that decorates the handler

    // push the request - this triggers the entire pipe, sends headers and displays any output
    $pipe->emit($resp->request);// use any Psr\Http\Message\ServerRequestInterface

    return $resp;
});
```

### Feature Console

Execute PHP code from the commandline, register commands before or during launching.

- `php cli <command> <..attr> <..b>`
- like: `php cli version` or `php cli help`

Create a command and automatically register it to the app:

```php
<?php
use Satellite\KernelConsole\Command;
use Satellite\KernelConsole\ConsoleEvent;

/**
 * @var GetOpt\Command $command
 */
$command = Command::create(
    'hi',
     static function(ConsoleEvent $evt) {
        error_log('Hi ' .
            
            ($evt->operands[0] && $evt->operands[0]->getValue() ?
                $evt->operands[0]->getValue() :
                'there')

            . '!');
    },
    [] // options
);

$command->addOperands([
    new GetOpt\Operand('name', GetOpt\Operand::OPTIONAL),
]);
```

And it is ready for execution:

```bash
php cli hi
# prints: Hi there!

php cli hi Folks
# prints: Hi Folks!
```

Create a command natively and register it manually to the app:

```php
<?php

use Satellite\KernelConsole\Console;
use Satellite\KernelConsole\ConsoleEvent;

$command = new GetOpt\Command(
    'hi', // name
    static function(ConsoleEvent $evt) {
         error_log('Hi ' .
             ($evt->operands[0] && $evt->operands[0]->getValue() ?
                 $evt->operands[0]->getValue() :
                 'there')
             . '!');
    }, 
    [] // options
);

$command->addOperands([
    new GetOpt\Operand('name', GetOpt\Operand::OPTIONAL),
]);

Console::addCommand('hi', $command);
```

For more details on how to use `GetOpt` to build commands with more details, options and operands see [getopt-php docs.](https://github.com/getopt-php/getopt-php).

### Feature DI

Satellite is **PSR-11** compatible and can be used with any Dependency Injection libray.

> Take note on the [optimized TypeHintContainerResolver](https://github.com/bemit/satellite/blob/master/src/Event/EventDispatcherTypeHintContainerResolver.php) for mixed typehinted static and injected params. This is used in `Event`'s invoke system.

Register a container to `Event` and it is accessible in every event handler.

The container must register itself for `Psr\Container\ContainerInterface`, this way it is automatically available for conditional registrations, like for the router.

```php
<?php
use Satellite\Event;

try {
    /**
     * Add any PSR-11 compatible container, the container must register itself as `ContainerInterface`
     *
     * @var Psr\Container\ContainerInterface $container
     */
    Event::useContainer($container);
} catch(\Exception $e) {
    error_log('launch: Event use Container failed');
    exit(2);
}
```

To enable the DI now also for the middleware request handler, type hint it at `RouteEvent` handler.

```php
<?php
use Satellite\Event;
use Satellite\KernelRoute\RouteEvent;
use Satellite\Response\RespondPipe;

Event::on(RouteEvent::class, static function(RouteEvent $resp, Psr\Container\ContainerInterface $container) {
    $pipe = new RespondPipe();

    // .. other middleware

    // e.g. using the PSR-11 compatible `Middlewares\RequestHandler` to handle the execution of matched routes
    $pipe->with(new Middlewares\RequestHandler($container));
    
    // .. other middleware, emitting

    return $resp;
});
```

Use the `orbiter/satellite-di` **experimental** package for ease of Setup:

```php
<?php
use Satellite\Event;

$container = new Satellite\DI\Container(
    getenv('env') === 'prod' ? __DIR__ . '/tmp/di' : null // cache dir or `null`, turn off on dev
);
$container->autowire(true);
$container->annotations(false);
try {
    Event::useContainer($container->container());
} catch(\Exception $e) {
    error_log('launch: Event use Container failed');
    exit(2);
}
```

## Download Build

> // todo

## License

This project is free software distributed under the **MIT License**.

See: [LICENSE](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository without the expectation of consideration.

***

Maintained by [Michael Becker](https://mlbr.xyz)
