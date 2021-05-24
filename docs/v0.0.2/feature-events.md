# Core + Events for Orbiter\Satellite 🛰️

- [Setup](../../README.md#setup)
    - [Config](../../README.md#config)
- [Implemented PSRs](../../README.md#psrs)
- [Used Packages](../../README.md#used-packages)
- [Features](README.md)
    - [Events](feature-events.md)
    - [Routing](feature-routing.md)
        - [Middleware](feature-middleware.md)
    - [Console](feature-console.md)
    - [DI](feature-di.md)
- [System Default Events](satellite-events.md)

## Feature Events

At the core is an event system that controls the flow of Satellite and lets you extend it easily.

Building event pipes and connecting your contexts through domain-oriented events makes it easy to build scalable and useful business software.

Any event consists of an `object`, that may have properties and methods, the object gets dispatched and passed along all listeners registered for that event.

Event listeners are registered with the class signature of the event and one callable (or InterOp resolvable).

### Event Store

`Event` provides a singleton store to the dispatcher and listener for direct use across the project.

```php
<?php
use Satellite\Event;

Event::on(string $event, callable $listener);
Event::dispatch(string $event);
Event::dispatcher(): EventDispatcherInterface;
```

### Event Listener

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

#### Event Listener Delegation

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
    $delegate = new Event\Delegate();

    $delegate->setHandler($evt->handler);
    $delegate->setEvent($evt);

    return $delegate;
});

// Using Dependency-Injection is recommended for better re-usability
// Type-Hint and receive a new object of the implementing class
Event::on(ConsoleEvent::class, static function($evt, Event\DelegateInterface $delegate) {
    $delegate->setHandler($evt->handler);
    $delegate->setEvent($evt);

    return $delegate;
});
```

> `Delegate` is a powerful function that should not be used too often, as it can easily lead to chaotic domain contexts.
>
> Good use-cases are for example:
>
> 1. Conditionally create/extract the handler that is responsible for the actual handling
> 2. **Generic** Decorator or Transformer functions, e.g. formatting and sanitizing of values that are already provided

### Event Dispatching

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

### Event Definition

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

### Inheritance of Events
 
If one event inherits another:

1. the registered listeners for the object are added
2. the parent classes of the object are fetched
3. for each parent class, the events are added
4. all events are executed first-in first-out

If you want to end after yours, the event must implement a StoppableEvent

### Event Dispatcher Instance 

You can get the actual dispatcher instance from the `Event` singleton.

```php
<?php
use Satellite\Event;

$dispatcher = Event::dispatcher();
$dispatcher->dispatch(<your-class>);
```
