<?php

use function DI\autowire;
use function DI\get;
use function DI\create;

return static function($config) {
    $is_prod = $_ENV['env'] === 'prod';
    return [
        Satellite\SatelliteAppInterface::class => autowire(Satellite\SatelliteApp::class),
        //
        // event handler
        Satellite\Event\EventListener::class => autowire(),
        Satellite\Event\EventListenerInterface::class => get(Satellite\Event\EventListener::class),
        Psr\EventDispatcher\ListenerProviderInterface::class => get(Satellite\Event\EventListener::class),
        Psr\EventDispatcher\EventDispatcherInterface::class => autowire(Satellite\Event\EventDispatcher::class),
        //
        // HTTP Servers & Clients
        Nyholm\Psr7\Factory\Psr17Factory::class => autowire(),
        Psr\Http\Message\RequestFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\ResponseFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\ServerRequestFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\StreamFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\UploadedFileFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\UriFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        //
        // annotations
        Doctrine\Common\Annotations\IndexedReader::class => autowire()
            ->constructorParameter('reader', get(Doctrine\Common\Annotations\AnnotationReader::class)),
        Doctrine\Common\Annotations\CachedReader::class => autowire()
            ->constructorParameter('reader', get(Doctrine\Common\Annotations\IndexedReader::class))
            ->constructorParameter(
                'cache',
                create(Doctrine\Common\Cache\ChainCache::class)
                    ->constructor([
                        create(Doctrine\Common\Cache\ArrayCache::class),
                        get(Doctrine\Common\Cache\PhpFileCache::class),
                    ])
            ),
        Doctrine\Common\Annotations\Reader::class => $is_prod ?
            get(Doctrine\Common\Annotations\CachedReader::class) :
            get(Doctrine\Common\Annotations\IndexedReader::class),
        Orbiter\AnnotationsUtil\CodeInfo::class => autowire()
            ->constructorParameter('file_cache', $is_prod ? $config['dir_tmp'] . '/codeinfo.cache' : null),
        Orbiter\AnnotationsUtil\AnnotationDiscovery::class => autowire(),
        Orbiter\AnnotationsUtil\AnnotationReader::class => autowire(),
        //
        // routing
        Satellite\Response\ResponsePipe::class => autowire(),
        Satellite\KernelRoute\Router::class => autowire(Satellite\KernelRoute\Router::class)
            ->constructorParameter('cache', $is_prod ? $config['dir_tmp'] . '/route.cache' : null),
        //
        // caches
        Doctrine\Common\Cache\PhpFileCache::class => autowire()
            ->constructorParameter('directory', $config['dir_tmp'] . '/common'),
        //
        // logger
        Psr\Log\LoggerInterface::class => autowire(Monolog\Logger::class)
            ->constructor('default')
            ->method('pushHandler', get(Monolog\Handler\StreamHandler::class)),
        Monolog\Handler\StreamHandler::class => autowire()
            ->constructor('php://stdout'),
    ];
};
