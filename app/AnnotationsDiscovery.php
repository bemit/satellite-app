<?php

namespace App;

use Doctrine\Common\Cache;
use DI\Annotation\Inject;
use Invoker\InvokerInterface;
use Orbiter\AnnotationsUtil\AnnotationDiscovery;
use Orbiter\AnnotationsUtil\CodeInfo;
use Psr\Container\ContainerInterface;
use Satellite\KernelConsole;
use Satellite\KernelRoute;
use Satellite\Response\ResponsePipe;
use Satellite\SatelliteAppInterface;

class AnnotationsDiscovery {
    public const CACHE_ANNOTATIONS_DISCOVERY = 'launcher:annotations_discovery';
    public const ANNOTATIONS_DISCOVERY = 'annotations';
    /**
     * @Inject
     * @var \Orbiter\AnnotationsUtil\AnnotationDiscovery
     */
    protected AnnotationDiscovery $discovery;
    /**
     * @Inject
     * @var \Doctrine\Common\Cache\PhpFileCache
     */
    protected Cache\PhpFileCache $cache;
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;
    /**
     * @Inject
     * @var InvokerInterface
     */
    protected InvokerInterface $invoker;

    public function __construct(CodeInfo $code_info, ContainerInterface $container) {
        $this->container = $container;
        $config = $container->get('config');
        if(isset($config['code_info'])) {
            foreach($config['code_info'] as $name => $paths) {
                $code_info->defineDirs($name, $paths);
            }
        }
        $code_info->process();
    }

    public function discover(SatelliteAppInterface $app) {
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

    public function bindCommands(KernelConsole\Console $console) {
        $this->container->set(KernelConsole\CommandDiscovery::CONTAINER_ID, $this->discovery->getDiscovered(KernelConsole\Annotations\Command::class));
        $this->invoker->call([KernelConsole\CommandDiscovery::class, 'registerAnnotations']);
        return $console;
    }

    public function bindRoutes(ResponsePipe $pipe) {
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
