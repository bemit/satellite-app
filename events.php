<?php

use Satellite\Event;
use Satellite\KernelConsole\Console;
use Satellite\KernelConsole\ConsoleEvent;
use Satellite\KernelRoute\RouteEvent;
use Satellite\KernelRoute\Router;
use Satellite\SystemLaunchEvent;

// Setup App specific automatic annotation registering
Event::on(SystemLaunchEvent::class, [App\Launcher::class, 'setup']);
Event::on(SystemLaunchEvent::class, [App\Launcher::class, 'setupConsole']);
Event::on(SystemLaunchEvent::class, [App\Launcher::class, 'setupRoute']);

// Console Handling
Event::on(SystemLaunchEvent::class, [Console::class, 'handle',]);
Event::on(ConsoleEvent::class, [App\Launcher::class, 'handleConsole']);

// Route Handling
Event::on(SystemLaunchEvent::class, [Router::class, 'handle',]);
Event::on(RouteEvent::class, [App\Launcher::class, 'handleRoute']);

Router::setCache(getenv('env') === 'prod' ? __DIR__ . '/tmp/route.cache' : null);
