<?php declare(strict_types=1);

use function DI\autowire;
use function DI\get;
use function DI\create;

return static function(array $config) {
    $is_prod = $config['is_prod'] ?? false;
    return [
        Satellite\Launch\SatelliteAppConfig::class => autowire()->constructor($config),
        //
        // event handler
        Satellite\Event\EventListenerInterface::class => autowire(Satellite\Event\EventListener::class),
        Satellite\Event\EventDispatcher::class => autowire()
            ->constructorParameter('listener', get(Psr\EventDispatcher\ListenerProviderInterface::class))
            ->constructorParameter('invoker', get(Invoker\InvokerInterface::class))
            ->constructorParameter(
                'profiler',
                    $config['profile']['events'] ?? false ?
                    create(Satellite\EventProfiler\EventProfiler::class)
                        ->constructor(
                        //create(Satellite\EventProfiler\EventProfilerReporterLog::class)
                            create(Satellite\EventProfiler\EventProfilerReporterFile::class)
                                ->constructor(
                                    ['dir' => $config['dir_tmp'] . '/profiles', 'pattern' => 'Y-m-d-H_i_s_u', 'prefix' => 'evt_']
                                ),
                        ) : null,
            ),
        Psr\EventDispatcher\ListenerProviderInterface::class => get(Satellite\Event\EventListenerInterface::class),
        Psr\EventDispatcher\EventDispatcherInterface::class => get(Satellite\Event\EventDispatcher::class),
        //
        // HTTP Servers & Clients
        Psr\Http\Client\ClientInterface::class => autowire(GuzzleHttp\Client::class),
        Nyholm\Psr7\Factory\Psr17Factory::class => autowire(),
        Psr\Http\Message\RequestFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\ResponseFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\ServerRequestFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\StreamFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\UploadedFileFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        Psr\Http\Message\UriFactoryInterface::class => get(Nyholm\Psr7\Factory\Psr17Factory::class),
        //
        // Cache
        Cache\Adapter\Filesystem\FilesystemCachePool::class => autowire(App\Lib\FilesystemCachePoolNormalized::class)
            ->constructor(
                create(League\Flysystem\Filesystem::class)
                    ->constructor(
                        create(League\Flysystem\Adapter\Local::class)->constructor($config['dir_tmp'] . '/common')
                    ),
            ),
        Psr\Cache\CacheItemPoolInterface::class => get(Cache\Adapter\Filesystem\FilesystemCachePool::class),
        //
        // annotations
        Doctrine\Common\Annotations\IndexedReader::class => autowire()
            ->constructorParameter('reader', get(Doctrine\Common\Annotations\AnnotationReader::class)),
        Doctrine\Common\Annotations\PsrCachedReader::class => autowire()
            ->constructorParameter('reader', get(Doctrine\Common\Annotations\IndexedReader::class))
            ->constructorParameter('cache', get(Psr\Cache\CacheItemPoolInterface::class)),
        Doctrine\Common\Annotations\Reader::class => $is_prod ?
            get(Doctrine\Common\Annotations\PsrCachedReader::class) :
            get(Doctrine\Common\Annotations\IndexedReader::class),
        Orbiter\AnnotationsUtil\CodeInfo::class => autowire()
            ->constructorParameter('file_cache', $is_prod ? $config['dir_tmp'] . '/codeinfo.cache' : null),
        Orbiter\AnnotationsUtil\AnnotationDiscovery::class => autowire(),
        Orbiter\AnnotationsUtil\AnnotationReader::class => autowire(),
        App\AnnotationsDiscovery::class => autowire()
            ->constructorParameter('code_info', get(Orbiter\AnnotationsUtil\CodeInfo::class))
            ->constructorParameter('container', get(Psr\Container\ContainerInterface::class))
            ->constructorParameter('cache', $is_prod ? get(Cache\Adapter\Filesystem\FilesystemCachePool::class) : null),
        //
        // routing
        Satellite\Response\ResponsePipe::class => autowire(),
        Satellite\KernelRoute\Router::class => autowire(Satellite\KernelRoute\Router::class)
            ->constructorParameter('cache', $is_prod ? $config['dir_tmp'] . '/route.cache' : null),
        //
        // logger
        Psr\Log\LoggerInterface::class => autowire(Monolog\Logger::class)
            ->constructor('default')
            ->method('pushHandler', get(Monolog\Handler\StreamHandler::class)),
        Monolog\Handler\StreamHandler::class => autowire()
            ->constructor('php://stdout', \Monolog\Level::Debug),
    ];
};
