<?php

require_once __DIR__ . '/_routes.php';

use Satellite\Event;
use Satellite\KernelConsole\Console;
use Satellite\KernelConsole\ConsoleEvent;
use Satellite\KernelRoute\Router;
use Satellite\KernelRoute\RouteEvent;
use Satellite\Response\RespondPipe;
use Satellite\SystemLaunchEvent;
use Satellite\System;

Event::on(SystemLaunchEvent::class, 'enableNiceDebug');

Event::on(SystemLaunchEvent::class, [Console::class, 'handle',]);

Event::on(ConsoleEvent::class, static function(ConsoleEvent $evt) {
    // todo: add di
    call_user_func($evt->handler);
    return $evt;
});

Event::on(SystemLaunchEvent::class, [Router::class, 'handle',]);

Event::on(RouteEvent::class, static function(RouteEvent $resp) {
    $pipe = new RespondPipe();
    $pipe->with(RespondPipe::ROUTER);
    $pipe->with(new \Middlewares\RequestHandler());
    $pipe->emit($resp);

    return $resp;
});

$satellite = new System();
$satellite->launch();

