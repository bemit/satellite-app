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
use Orbiter\AnnotationsUtil\CodeInfo;
use Doctrine\Common\Cache;
use DI\ContainerBuilder;
use function DI\autowire;

if(getenv('env') !== 'prod') {
    Event::on(SystemLaunchEvent::class, 'enableNiceDebug');
}

// Setup Console
Event::on(SystemLaunchEvent::class, static function(SystemLaunchEvent $exec, Cache\PhpFileCache $cache, DI\Container $container) {
    if($exec->cli && getenv('env') === 'prod' && $cache->contains('commands')) {
        $container->set('commands', $cache->fetch('commands'));

        return $exec;
    }

    $exec = Event::execute([Satellite\KernelConsole\CommandDiscovery::class, 'discoverByAnnotation',], $exec);

    if(
        $exec->cli &&
        getenv('env') === 'prod' &&
        !$cache->contains('commands') &&
        $container->has('commands')
    ) {
        $commands = $container->get('commands');
        $cache->save('commands', $commands);
    }

    $exec = Event::execute([Satellite\KernelConsole\CommandDiscovery::class, 'registerAnnotations',], $exec);

    return $exec;
});

Event::on(SystemLaunchEvent::class, [Console::class, 'handle',]);

Event::on(ConsoleEvent::class, static function($evt) {
    $delegate = new Event\Delegate();
    $delegate->setHandler($evt->handler);
    $delegate->setEvent($evt);

    return $delegate;
});

// Setup Routing
Router::setCache(getenv('env') === 'prod' ? __DIR__ . '/tmp/route.cache' : null);
Event::on(SystemLaunchEvent::class, [Router::class, 'handle',]);

Event::on(RouteEvent::class, static function(RouteEvent $resp, ContainerInterface $container_builder) {
    $pipe = new RespondPipe();

    $pipe->with((new Middlewares\JsonPayload())
        ->associative(false)
        ->depth(64));
    $pipe->with(new Middlewares\UrlEncodePayload());

    $pipe->with($resp->router);

    $pipe->with(new Middlewares\RequestHandler($container_builder));
    $pipe->emit($resp->request);

    return $resp;
});

/**
 * General Path Configurations for DI and Annotations
 *
 * @var $config
 */
$config = [
    // Folders containing annotations
    'annotations' => [
        // PSR4\Namespace => abs/Path
        'Satellite\KernelConsole\Annotations' => __DIR__ . '/vendor/orbiter/satellite-console/src/Annotations',
        'Lib' => __DIR__ . '/lib',
    ],
    // annotations to ignore, Doctrine\Annotations applies a default filter
    'annotations_ignore' => [
        'dummy',
    ],
    // Folders compiled into DI-Container
    'di_services' => [
        __DIR__ . '/app',
        __DIR__ . '/lib',
    ],
    // Folders searched for infos about to be used as annotation discovery
    'di_annotation' => [
        __DIR__ . '/app',
        __DIR__ . '/lib',
    ],
];

// Setup Annotations
foreach($config['annotations'] as $annotation_ns => $annotation_ns_dir) {
    AnnotationsUtil::registerPsr4Namespace($annotation_ns, $annotation_ns_dir);
}
foreach($config['annotations_ignore'] as $annotation_ig) {
    Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($annotation_ig);
}

AnnotationsUtil::useReader(
    AnnotationsUtil::createReader(getenv('env') === 'prod' ? __DIR__ . '/tmp/annotations' : null)
);

// Setup DI
$container_builder = new ContainerBuilder();
$container_builder->useAutowiring(true);
$container_builder->useAnnotations(true);

if(getenv('env') === 'prod') {
    $container_builder->enableCompilation(__DIR__ . '/tmp/di');
}

// Setup Annotation Helper
$code_info = new CodeInfo();
if(getenv('env') === 'prod') {
    $code_info->enableFileCache(__DIR__ . '/tmp/codeinfo.cache');
}
$code_info->defineDirs('services', $config['di_services']);
$code_info->defineDirs('annotations', $config['di_annotation']);
$code_info->process();

// Defining DI Services
$cache = new Cache\PhpFileCache(__DIR__ . '/tmp/php_cache');
$definitions = [
    System::class => DI\autowire(System::class),
    CodeInfo::class => $code_info,
    Cache\PhpFileCache::class => $cache,
];

$services = $code_info->getClassNames('services');

foreach($services as $service) {
    $definitions[$service] = autowire($service);
}

$container_builder->addDefinitions($definitions);

// Building Container
try {
    $container = $container_builder->build();
} catch(\Exception $e) {
    error_log('launch: Event build Container failed');
    exit(2);
}

Event::useContainer($container);

// Dispatching the Launch
$container->call([System::class, 'launch']);
