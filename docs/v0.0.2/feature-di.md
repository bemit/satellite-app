# DI for Orbiter\Satellite 🛰️

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

## Feature DI

Satellite is **PSR-11** compatible and can be used with any Dependency Injection library, for handling the execution `php-di/invoker` is bundled.

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
     * @var Psr\Container\ContainerInterface $con_builder
     */
    Event::useContainer($con_builder);
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

Event::on(RouteEvent::class, static function(RouteEvent $resp, Psr\Container\ContainerInterface $con_builder) {
    $pipe = new RespondPipe();

    // .. other middleware

    // e.g. using the PSR-11 compatible `Middlewares\RequestHandler` to handle the execution of matched routes
    $pipe->with(new Middlewares\RequestHandler($con_builder));
    
    // .. other middleware, emitting

    return $resp;
});
```

## Dependency Injection

Use: `php-di/php-di`.

### Setup DI

See PHP-DI docs for details:

- [Setup Definitions](http://php-di.org/doc/php-definitions.html)
- [Included Annotations](http://php-di.org/doc/annotations.html)

```php
<?php
use Satellite\Event;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

$con_builder = new ContainerBuilder();
$con_builder->useAutowiring(true);
$con_builder->useAnnotations(true);
if(getenv('env') === 'prod') {
    $con_builder->enableCompilation(__DIR__ . '/tmp/di');
}

$con_builder->addDefinitions([
    ContainerInterface::class => DI\autowire(get_class($con_builder)),// must be provided for Container
    Event\DelegateInterface::class => DI\autowire(Event\Delegate::class),// optional usage for Event\Delegate
]);

try {
    Event::useContainer($con_builder->build());
} catch(\Exception $e) {
    error_log('launch: Event use Container failed');
    exit(2);
}
```
