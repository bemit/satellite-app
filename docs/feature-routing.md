# Routing for Orbiter\Satellite 🛰️

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

## Feature Routing

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

### Cached Router

Enable the cached router before the launch event, `string` with path to file or `null`. Default is off (`null`).

```php
<?php
use Satellite\KernelRoute\Router;

Router::setCache(getenv('env') === 'prod' ? __DIR__ . '/tmp/route.cache' : null);
```
