<?php

namespace App;

use DI\Container;
use Invoker\InvokerInterface;
use Orbiter\AnnotationsUtil\AnnotationDiscovery;
use Orbiter\AnnotationsUtil\CodeInfo;
use Psr\Cache\CacheItemPoolInterface;
use Satellite\KernelConsole;
use Satellite\KernelRoute;
use Satellite\Response\ResponsePipe;
use Satellite\Launch\SatelliteAppInterface;

class AnnotationsDiscovery {
    public const CACHE_ANNOTATIONS_DISCOVERY = 'launcher.annotations_discovery';
    public const ANNOTATIONS_DISCO_CONSOLE = 'annotations_disco_console';
    public const ANNOTATIONS_DISCO_ROUTE = 'annotations_disco_route';

    protected AnnotationDiscovery $discovery;
    protected InvokerInterface $invoker;

    protected ?CacheItemPoolInterface $cache;
    protected Container $container;
    protected CodeInfo $code_info;

    public function __construct(
        CodeInfo                $code_info,
        Container               $container,
        InvokerInterface        $invoker,
        AnnotationDiscovery     $discovery,
        ?CacheItemPoolInterface $cache = null,
    ) {
        $this->code_info = $code_info;
        $this->container = $container;
        $this->invoker = $invoker;
        $this->discovery = $discovery;
        $this->cache = $cache;
    }

    public function discover(SatelliteAppInterface $app): SatelliteAppInterface {
        $cache_item = null;
        if($this->cache) {
            $cache_item = $this->cache->getItem(static::CACHE_ANNOTATIONS_DISCOVERY);
            if($cache_item->isHit()) {
                $this->discovery->setDiscovered($cache_item->get());
                return $app;
            }
        }

        $discovery_flags = $this->code_info->getFlags();
        foreach($discovery_flags as $discovery_flag) {
            $this->discovery->discoverByAnnotation(
                $this->code_info->getClassNames($discovery_flag)
            );
        }

        if($cache_item) {
            $cache_item->set($this->discovery->getAll());
            $this->cache->save($cache_item);
        }
        return $app;
    }

    public function bindCommands(KernelConsole\Console $console): KernelConsole\Console {
        $kernel_console_disco = $this->container->get(KernelConsole\CommandDiscovery::class);
        $kernel_console_disco->registerAnnotations(
            $this->discovery->getDiscovered(KernelConsole\Annotations\Command::class),
        );
        return $console;
    }

    public function bindRoutes(ResponsePipe $pipe): ResponsePipe {
        $kernel_route_disco = $this->container->get(KernelRoute\RouteDiscovery::class);
        $kernel_route_disco->registerAnnotations([
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Get::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Route::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Post::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Patch::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Put::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Delete::class)),
        ]);
        return $pipe;
    }
}
