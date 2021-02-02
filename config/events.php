<?php

use Satellite\Event\EventListenerInterface;

return static function(EventListenerInterface $event, Psr\Container\ContainerInterface $container): void {
    $satellite_app = get_class($container->get(Satellite\SatelliteAppInterface::class));
    $event->on($satellite_app, [App\AnnotationsDiscovery::class, 'discover']);
    $event->on($satellite_app, [App\App::class, 'launch']);

    $event->on(Satellite\KernelConsole\Console::class, [App\AnnotationsDiscovery::class, 'bindCommands']);

    $event->on(Satellite\Response\ResponsePipe::class, [App\AnnotationsDiscovery::class, 'bindRoutes']);
    $event->on(
        Satellite\Response\ResponsePipe::class,
        static function(Satellite\Response\ResponsePipe $pipe, Invoker\InvokerInterface $invoker) {
            $pipeline = require __DIR__ . '/../config/pipeline.php';
            $invoker->call($pipeline);

            return $pipe;
        }
    );
};
