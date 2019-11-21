# DI for Orbiter\Satellite 🛰️

- [Setup](../#setup)
    - [Config](../#config)
- [Implemented PSRs](../#psrs)
- [Used Packages](../#used-packages)

## Features
- [Events](feature-events.md)
- [Routing](feature-routing.md)
    - [Middleware](feature-middleware.md)
- [Console](feature-console.md)
- [DI](feature-di.md)

## Feature DI

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

## Dependency Injection

Uses the `orbiter/satellite-di` package for ease of Setup, includes `php-di/php-di`.

### Setup DI

See PHP-DI docs for details:

- [Setup Definitions](http://php-di.org/doc/php-definitions.html)
- [Included Annotations](http://php-di.org/doc/annotations.html)

```php
<?php
use Satellite\Event;
use Satellite\DI\Container;
use Psr\Container\ContainerInterface;

$container = new Container();
$container->useAutowiring(true);
$container->useAnnotations(true);
if(getenv('env') === 'prod') {
    $container->enableCompilation(__DIR__ . '/tmp/di');
}

$container->addDefinitions([
    ContainerInterface::class => DI\autowire(get_class($container)),// must be provided for Container
    Event\DelegateInterface::class => DI\autowire(Event\Delegate::class),// optional usage for Event\Delegate
]);

try {
    Event::useContainer($container->container());
} catch(\Exception $e) {
    error_log('launch: Event use Container failed');
    exit(2);
}
```
