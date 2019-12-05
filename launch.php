<?php

use Satellite\Event;

use Satellite\System;
use Orbiter\AnnotationsUtil\AnnotationsUtil;
use Orbiter\AnnotationsUtil\CodeInfo;
use Doctrine\Common\Cache;
use DI\ContainerBuilder;
use function DI\autowire;

/*
 * General Path Configurations, DI and Annotations
 */
$tmp_root = __DIR__ . '/tmp';

$config_annotation = [
    // Folders containing annotations
    'psr4' => [
        // PSR4\Namespace => abs/Path
        'Satellite\KernelConsole\Annotations' => __DIR__ . '/vendor/orbiter/satellite-console/src/Annotations',
        'Satellite\KernelRoute\Annotations' => __DIR__ . '/vendor/orbiter/satellite-route/src/Annotations',
        'Lib' => __DIR__ . '/lib',
    ],
    // annotations to ignore, Doctrine\Annotations applies a default filter
    'ignore' => [
        'dummy',
    ],
];

$config_di = [
    // Folders compiled into DI-Container
    'services' => [
        __DIR__ . '/app',
        __DIR__ . '/lib',
        //__DIR__ . '/vendor/orbiter/annotations-util/src',
    ],
    // Folders searched for infos to be used in annotation discovery
    'annotation' => [
        __DIR__ . '/app',
        __DIR__ . '/lib',
    ],
];

// Setup Annotations
foreach($config_annotation['psr4'] as $annotation_ns => $annotation_ns_dir) {
    AnnotationsUtil::registerPsr4Namespace($annotation_ns, $annotation_ns_dir);
}
foreach($config_annotation['ignore'] as $annotation_ig) {
    Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($annotation_ig);
}

AnnotationsUtil::useReader(
    AnnotationsUtil::createReader(getenv('env') === 'prod' ? $tmp_root . '/annotations' : null)
);

// Setup DI
$container_builder = new ContainerBuilder();
$container_builder->useAutowiring(true);
$container_builder->useAnnotations(true);

if(getenv('env') === 'prod') {
    $container_builder->enableCompilation($tmp_root . '/di');
}

// Setup Annotation Helper
$code_info = new CodeInfo();
if(getenv('env') === 'prod') {
    $code_info->enableFileCache($tmp_root . '/codeinfo.cache');
}
$code_info->defineDirs('services', $config_di['services']);
$code_info->defineDirs('annotations', $config_di['annotation']);
$code_info->process();

// Defining DI Services
$definitions = [
    System::class => DI\autowire(System::class),
    CodeInfo::class => $code_info,
    Cache\PhpFileCache::class => DI\autowire()
        ->constructorParameter('directory', $tmp_root . '/php_cache'),
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
