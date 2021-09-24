<?php

namespace App;

use DI\Container;
use Doctrine\Common\Cache;
use DI\Annotation\Inject;
use Invoker\InvokerInterface;
use Orbiter\AnnotationsUtil\AnnotationDiscovery;
use Orbiter\AnnotationsUtil\CodeInfo;
use Satellite\KernelConsole;
use Satellite\KernelRoute;
use Satellite\Response\ResponsePipe;
use Satellite\SatelliteAppInterface;

class AnnotationsDiscovery {
    public const CACHE_ANNOTATIONS_DISCOVERY = 'launcher:annotations_discovery';
    public const ANNOTATIONS_DISCOVERY = 'annotations';
    /**
     * @Inject
     */
    protected AnnotationDiscovery $discovery;
    /**
     * @Inject
     */
    protected Cache\PhpFileCache $cache;
    /**
     * @var Container
     */
    protected Container $container;
    /**
     * @Inject
     */
    protected InvokerInterface $invoker;

    /**
     * @param CodeInfo $code_info
     * @param Container $container
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \JsonException
     * @throws \Orbiter\AnnotationsUtil\CodeInfoCacheFileException
     */
    public function __construct(CodeInfo $code_info, Container $container) {
        $this->container = $container;
        $config = $container->get('config');
        if(isset($config['code_info'])) {
            foreach($config['code_info'] as $name => $paths) {
                $code_info->defineDirs($name, $paths);
            }
        }
        $code_info->process();
    }

    public function discover(SatelliteAppInterface $app): SatelliteAppInterface {
        if($_ENV['env'] === 'prod' && $this->cache->contains(static::CACHE_ANNOTATIONS_DISCOVERY)) {
            $this->discovery->setDiscovered($this->cache->fetch(static::CACHE_ANNOTATIONS_DISCOVERY));
            return $app;
        }

        // Discovering Annotations on classes found for CodeInfo group `annotations`
        $this->discovery->discoverByAnnotation(self::ANNOTATIONS_DISCOVERY);

        if($_ENV['env'] === 'prod') {
            $this->cache->save(static::CACHE_ANNOTATIONS_DISCOVERY, $this->discovery->getAll());
        }
        return $app;
    }

    public function bindCommands(KernelConsole\Console $console): KernelConsole\Console {
        $this->container->set(KernelConsole\CommandDiscovery::CONTAINER_ID, $this->discovery->getDiscovered(KernelConsole\Annotations\Command::class));
        $this->invoker->call([KernelConsole\CommandDiscovery::class, 'registerAnnotations']);
        return $console;
    }

    public function bindRoutes(ResponsePipe $pipe): ResponsePipe {
        $this->container->set(KernelRoute\RouteDiscovery::CONTAINER_ID, [
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Get::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Route::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Post::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Put::class)),
            ...($this->discovery->getDiscovered(KernelRoute\Annotations\Delete::class)),
        ]);
        $this->invoker->call([KernelRoute\RouteDiscovery::class, 'registerAnnotations']);
        return $pipe;
    }
}
