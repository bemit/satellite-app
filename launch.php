<?php

use Orbiter\AnnotationsUtil\AnnotationUtil;
use DI\ContainerBuilder;

date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

(static function() {
    $cli = PHP_SAPI === 'cli';
    $config = (require __DIR__ . '/config/config.php')();

    if($_ENV['env'] !== 'prod') {
        Satellite\Whoops\NiceDebug::enable($cli);
    }

    // Setup DI
    $container_builder = new ContainerBuilder();
    $container_builder->useAutowiring(true);
    $container_builder->useAnnotations(true);

    if($_ENV['env'] === 'prod') {
        $container_builder->enableCompilation($config['dir_tmp'] . '/di');
    }

    // Defining DI Services
    $dependencies = (require __DIR__ . '/config/dependencies.php')($config);
    $dependencies['config'] = $config;

    $container_builder->addDefinitions($dependencies);

    // Building Container
    try {
        $container = $container_builder->build();
    } catch(\Exception $e) {
        error_log('Satellite launch: container building failed: ' . $e->getMessage());
        exit(2);
    }

    /**
     * @var $invoker \Invoker\Invoker
     */
    $invoker = $container->get(\Invoker\Invoker::class);
    $invoker->getParameterResolver()->prependResolver(
        new Satellite\InvokerTypeHintContainerResolver($container)
    );

    // Setup Annotations
    foreach($config['annotation']['psr4'] as $annotation_ns => $annotation_ns_dir) {
        AnnotationUtil::registerPsr4Namespace($annotation_ns, $annotation_ns_dir);
    }
    foreach($config['annotation']['ignore'] as $annotation_ig) {
        Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName($annotation_ig);
    }

    // Attach events
    $events = require __DIR__ . '/config/events.php';
    $invoker->call($events);

    /**
     * @var \Satellite\SatelliteAppInterface $app
     */
    $app = $container->get(Satellite\SatelliteAppInterface::class);
    $app->launch($cli);
})();

