<?php

use Psr\Container\ContainerInterface;
use Satellite\Event;

use Satellite\SystemLaunchEvent;
use Satellite\System;

use Satellite\KernelConsole\Console;
use Satellite\KernelConsole\ConsoleEvent;
use Satellite\KernelRoute\Router;
use Satellite\KernelRoute\RouteEvent;
use Satellite\Response\RespondPipe;
use Orbiter\AnnotationsUtil\AnnotationsUtil;
use Satellite\DI\Container;

if(getenv('env') !== 'prod') {
    Event::on(SystemLaunchEvent::class, 'enableNiceDebug');
}

// Setup Console
Event::on(SystemLaunchEvent::class, [Console::class, 'handle',]);

Event::on(ConsoleEvent::class, static function($evt, Event\DelegateInterface $delegate) {
    $delegate->setHandler($evt->handler);
    $delegate->setEvent($evt);

    return $delegate;
});

// Setup Routing
Router::setCache(getenv('env') === 'prod' ? __DIR__ . '/tmp/route.cache' : null);
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

// Setup Annotations
AnnotationsUtil::registerPsr4Namespace('Lib', __DIR__ . '/lib');
Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('dummy');

AnnotationsUtil::useReader(
    AnnotationsUtil::createReader(getenv('env') === 'prod' ? __DIR__ . '/tmp/annotations' : null)
);

// Setup DI
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

// Launch your Satellite!
$satellite = new System();
$satellite->launch();

