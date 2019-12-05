<?php

namespace App;

use Psr\Container\ContainerInterface;
use Satellite\Event;
use Satellite\KernelRoute;
use Satellite\KernelConsole;
use DI\Annotation\Inject;
use Doctrine\Common\Cache;
use Orbiter\AnnotationsUtil\AnnotationDiscovery;
use Satellite\KernelRoute\RouteEvent;
use Satellite\Response\RespondPipe;
use Satellite\SystemLaunchEvent;
use DI;
use Middlewares;

class Launcher {
    public const CACHE_ANNOTATIONS_DISCOVERY = 'launcher:annotations_discovery';
    public const CACHE_COMMANDS = 'launcher:commands';
    public const CACHE_ROUTES = 'launcher:routes';

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
     * @Inject
     * @var \DI\Container
     */
    protected DI\Container $container;

    protected $discovering = [
        KernelRoute\Annotations\Route::class,
        KernelRoute\Annotations\Post::class,
        KernelConsole\Annotations\Command::class,
    ];

    public function setup(SystemLaunchEvent $exec) {
        if(getenv('env') === 'prod' && $this->cache->contains(static::CACHE_ANNOTATIONS_DISCOVERY)) {
            $this->discovery->setDiscovered($this->cache->fetch(static::CACHE_ANNOTATIONS_DISCOVERY));
            return $exec;
        }

        foreach($this->discovering as $disco) {
            $this->discovery->addDiscover($disco);
        }

        // Discovering Annotations on classes found for CodeInfo group `annotations`
        $this->discovery->discoverByAnnotation('annotations');

        return $exec;
    }

    public function setupConsole(SystemLaunchEvent $exec) {
        $this->container->set('commands', $this->discovery->getDiscovered()[KernelConsole\Annotations\Command::class]);

        $exec = Event::execute([KernelConsole\CommandDiscovery::class, 'registerAnnotations',], $exec);

        return $exec;
    }

    public function setupRoute(SystemLaunchEvent $exec) {
        // register the discovered
        $this->container->set('routes', [
            ...$this->discovery->getDiscovered()[KernelRoute\Annotations\Route::class],
            ...$this->discovery->getDiscovered()[KernelRoute\Annotations\Post::class],
        ]);

        $exec = Event::execute([KernelRoute\RouteDiscovery::class, 'registerAnnotations',], $exec);

        return $exec;
    }

    public function handleConsole(KernelConsole\ConsoleEvent $evt) {
        // Getting the matched console command handler and delegating the execution
        $delegate = new Event\Delegate();
        $delegate->setHandler($evt->handler);
        $delegate->setEvent($evt);

        return $delegate;
    }

    public function handleRoute(RouteEvent $resp, ContainerInterface $container_builder) {
        $pipe = new RespondPipe();

        $pipe->with((new Middlewares\JsonPayload())
            ->associative(false)
            ->depth(64));
        $pipe->with(new Middlewares\UrlEncodePayload());

        $pipe->with($resp->router);

        $pipe->with(new Middlewares\RequestHandler($container_builder));
        $pipe->emit($resp->request);

        return $resp;
    }
}
