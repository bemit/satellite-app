<?php

use Satellite\KernelRoute\Router;
use Satellite\Response\Respond;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\RequestHandlerInterface;

Router::addRoute('home', 'GET', '/', static function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
    return '<!doctype HTML><html><h2 style="font-family: sans-serif">Satellite 🛰️</h2></html>';
});

Router::addRoute('demo-json', 'GET', '/json',
    static function(ServerRequestInterface $request, RequestHandlerInterface $handler) {

        return Respond::json(['Demo', 'Data', 'As', 'JSON'], $request, $handler);
    }
);

Router::addGroup(
    'api', '/api',
    [
        'auth' => Router::post('/auth', static function() {

        }),
    ]
);
