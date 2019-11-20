<?php

use Satellite\Event;

use Satellite\SystemLaunchEvent;
use Satellite\System;

use Satellite\KernelConsole\Console;
use Satellite\KernelConsole\ConsoleEvent;
use Satellite\KernelRoute\Router;
use Satellite\KernelRoute\RouteEvent;
use Satellite\Response\RespondPipe;

if(getenv('env') !== 'prod') {
    Event::on(SystemLaunchEvent::class, 'enableNiceDebug');
}

Event::on(SystemLaunchEvent::class, [Console::class, 'handle',]);

Event::on(ConsoleEvent::class, static function($evt) {
    return new Event\Delegate($evt->handler, $evt);
});

Event::on(SystemLaunchEvent::class, [Router::class, 'handle',]);

Event::on(RouteEvent::class, static function(RouteEvent $resp, Psr\Container\ContainerInterface $container) {
    $pipe = new RespondPipe();

    $pipe->with((new Middlewares\JsonPayload())
        ->associative(false)
        ->depth(64));
    $pipe->with(new Middlewares\UrlEncodePayload());

    $pipe->with($resp->router);

    $pipe->with(new Middlewares\RequestHandler($container));
    $pipe->emit($resp->request);

    return $resp;
});

$container = new Satellite\DI\Container(getenv('env') === 'prod' ? __DIR__ . '/tmp/di' : null);
$container->autowire(true);
$container->annotations(false);
try {
    Event::useContainer($container->container());
} catch(\Exception $e) {
    error_log('launch: Event use Container failed');
    exit(2);
}

$satellite = new System();
$satellite->launch();

