<?php

use function DI\autowire;
use function DI\get;

return static function($config) {
    return [
        \Satellite\SatelliteAppInterface::class => autowire(\Satellite\SatelliteApp::class),
        //
        // event handler
        \Satellite\Event\EventListener::class => autowire(Satellite\Event\EventListener::class),
        \Satellite\Event\EventListenerInterface::class => get(Satellite\Event\EventListener::class),
        \Psr\EventDispatcher\ListenerProviderInterface::class => get(Satellite\Event\EventListener::class),
        \Psr\EventDispatcher\EventDispatcherInterface::class => autowire(Satellite\Event\EventDispatcher::class),
        //
        // routing
        \Satellite\KernelRoute\Router::class => autowire(\Satellite\KernelRoute\Router::class)
            ->constructorParameter('cache', $_ENV['env'] === 'prod' ? $config['dir_tmp'] . '/route.cache' : null),
        //
        // caches
        \Doctrine\Common\Cache\PhpFileCache::class => autowire()
            ->constructorParameter('directory', $config['dir_tmp'] . '/php_cache'),
        //
        // logger
        Psr\Log\LoggerInterface::class => autowire(Monolog\Logger::class)
            ->constructor('default')
            ->method('pushHandler', get(Monolog\Handler\StreamHandler::class)),
        Monolog\Handler\StreamHandler::class => autowire()
            ->constructor('php://stdout'),
    ];
};
