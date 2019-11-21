# Middleware Pipe for Orbiter\Satellite 🛰️

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

## Feature Middleware

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
