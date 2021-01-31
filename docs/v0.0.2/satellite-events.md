# Events dispatched by Orbiter\Satellite 🛰️

- [Setup](../README.md#setup)
    - [Config](../README.md#config)
- [Implemented PSRs](../README.md#psrs)
- [Used Packages](../README.md#used-packages)
- [Features](README.md)
    - [Events](feature-events.md)
    - [Routing](feature-routing.md)
        - [Middleware](feature-middleware.md)
    - [Console](feature-console.md)
    - [DI](feature-di.md)
- [System Default Events](satellite-events.md)

Satellite system communicates inter-code [with events](feature-events.md) that are dispatched somewhere, data is added, one of the listeners executing the logic needed to be done to handle the event.

These Events are used through the default Satellite framework to integrate all parts:

> todo: interfaces for all packages and their satellite-specific additions must be created (like for route store)

## SystemLaunchEvent

Originates from [orbiter/satellite](https://github.com/bemit/satellite) and is used to add logic pieces able to handle the initial run.

It is useful for e.g. adding the console and route feature or general setup tasks.

Origin replacement just needs to implement the `cli` detection.

```php
<?php

namespace Satellite;

use Psr\EventDispatcher\StoppableEventInterface;
use Satellite\Event\StoppableEvent;

class SystemLaunchEvent implements StoppableEventInterface {
    use StoppableEvent;

    /**
     * @var bool contains if it runs for the cli, when not it is considered as route
     */
    public $cli = false;
}
```

## ConsoleEvent

Originates from [orbiter/satellite-console](https://github.com/bemit/satellite-console).
 
Is used to add any command-line library.

Origin replacements must implement the unified `Satellite\KernelConsole\Console` store, and a CLI handling framework connector.

```php
<?php

namespace Satellite\KernelConsole;

use Psr\EventDispatcher\StoppableEventInterface;
use Satellite\Event\StoppableEvent;

class ConsoleEvent implements StoppableEventInterface {
    use StoppableEvent;

    /**
     * @var string|callable|array anything that the event system invoker can handle (orbiter/satellite, optional di)
     */
    public $handler;
    /**
     * @var array short and long options for like `php cli db [...] -d|--debug`
     */
    public $options;
    /**
     * @var array positional values for like `php cli db tables list`
     */
    public $operands;
}
```

## RouteEvent

Originates from [orbiter/satellite-route](https://github.com/bemit/satellite-route).

Is used to build the middleware-pipe out of the generated router and request.

Origin replacements must implement the unified `Satellite\KernelRoute\Router` store, a PSR-7,17 request generation, setup of any PSR-15 router middleware.

```php
<?php

namespace Satellite\KernelRoute;

use Psr\EventDispatcher\StoppableEventInterface;
use Satellite\Event\StoppableEvent;

class RouteEvent implements StoppableEventInterface {
    use StoppableEvent;

    /**
     * @var \Psr\Http\Server\MiddlewareInterface the router middleware
     */
    public $router;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface the generated server request
     */
    public $request;
}
```
