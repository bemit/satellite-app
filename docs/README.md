# Orbiter\Satellite Features 🛰️

- [Setup](../README.md#setup)
    - [Config](../README.md#config)
- [Implemented PSRs](../README.md#psrs)
- [Used Packages](../README.md#used-packages)

Satellite integrates into a micro framework to rapidly build PHP server apps, mostly - but not limited - for non-human clients.

The features are supplied by different packages, swappable through Events and Container:

- [Features](README.md)
    - [Events](feature-events.md)
    - [Routing](feature-routing.md)
        - [Middleware](feature-middleware.md)
    - [Console](feature-console.md)
    - [DI](feature-di.md)
- [System Default Events](satellite-events.md)

## Setup Annotations

We recommend using [Orbiter\AnnotationUtil](https://github.com/bemit/orbiter-annotations-util) for ease of setup and access of `doctrine/annotation`.

## Extend Event Dispatcher or Invoker

To extend the core `Satellite\Event` define another class that should be generated as singleton.

This lets you control fully what event dispatcher and event listener will be used.

- `Satellite\Event` implements `Satellite\EventStoreSingleton`
- `Satellite\EventDispatcher` implements `Psr\EventDispatcher\EventDispatcherInterface`, PSR-14
    - also implements `Satellite\Event\EventDispatcherInterface` to add PSR-11 support
- `Satellite\EventListener` implements `Psr\EventDispatcher\ListenerProviderInterface`, PSR-14
    - also implements `Satellite\Event\EventListenerInterface` to add PSR-11 support

```php
<?php
use Satellite\Event;

// must be called before any other call to `Event`
Event::setSingletonClass(EventCustom::class);
```

Now you can control within your `EventCustom` class what should be modified.
 
Take a look at the [class file](https://github.com/bemit/satellite/blob/master/src/Event.php), here is minimal example:

```php
<?php

namespace App;

use Satellite\Event;
use Satellite\Event\EventDispatcher;
use Satellite\Event\EventListener;

/**
 * Modified Singleton Interface for a Satellite App Event Storage.
 */
class EventCustom extends Event {

    /**
     * On modified EventStore Singleton that EXTENDS the original,
     * this is the bare minimum with what you can change anything, through switching the classes.
     */
    protected function __construct() {
        // here you can e.g. use another listener or another dispatcher at all
        $this->dispatcher = new EventDispatcher(new EventListener());
    }
}
```

If you want to switch/modify the Invoker you can extend the default `EventDispatcher` and overwrite it in the above class, and define it like:

```php
<?php

namespace App;

use Invoker\Invoker;
use Satellite\Event\EventDispatcher;
use Satellite\Event\EventDispatcherTypeHintContainerResolver;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherCustom extends EventDispatcher {
    /**
     * @var \Invoker\Invoker
     */
    protected $invoker;

    public function __construct(ListenerProviderInterface $listener) {
        // not calling parent class construct - when we want to change the invoker class at all
        $this->listener = $listener;
        $this->invoker = new Invoker();
    }

    public function useContainer(ContainerInterface $container) {
        if($this->container) {
            // only one-time
            return;
        }

        $this->container = $container;

        // setup invoker with container and resolvers that should be used
        $this->invoker = new Invoker(null, $container);
        $this->invoker->getParameterResolver()
                      ->prependResolver(
                          new EventDispatcherTypeHintContainerResolver($container)
                      );
    }
}
``` 
