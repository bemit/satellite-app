<?php declare(strict_types=1);

use \Satellite\System;
use \Satellite\Config;

return static function() {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__) . '/');
    $dotenv->safeLoad();

    // if no env is set, pretend it is production
    if(!isset($_ENV['env']) || empty($_ENV['env'])) {
        $_ENV['env'] = 'prod';
    }

    $is_prod = $_ENV['env'] === 'prod';
    $dir_tmp = dirname(__DIR__) . '/tmp';
    if(!is_dir($dir_tmp)) {
        mkdir($dir_tmp, 0775, true);
    }

    return (new Config\ConfigAggregator($is_prod ? $dir_tmp . '/config_aggregated.php' : null))
        ->append(
            [
                'dir_tmp' => $dir_tmp,
                'is_prod' => $is_prod,
                'whoops' => !$is_prod,
                'info' => [
                    'env' => $_ENV['env'],
                    'app_env' => $_ENV['APP_ENV'] ?? 'prod',
                ],
                System\SetupContainer::CONFIG_CONTAINER => [
                    'compile' => $is_prod,
                ],
                System\SetupAnnotations::CONFIG_ANNOTATION => [
                    // Folders containing annotations
                    'psr4' => [
                        // PSR4\Namespace => abs/Path
                        'Satellite\KernelConsole\Annotations' => dirname(__DIR__) . '/vendor/orbiter/satellite-console/src/Annotations',
                        'Satellite\KernelRoute\Annotations' => dirname(__DIR__) . '/vendor/orbiter/satellite-route/src/Annotations',
                    ],
                    // annotations to ignore, Doctrine\Annotations applies a default filter
                    'ignore' => [
                        'dummy',
                    ],
                ],
                // Code Analyzer for Annotations Discovery
                System\SetupAnnotations::CONFIG_CODE_INFO => [
                    [
                        'folder' => dirname(__DIR__) . '/app/Commands',
                        'flags' => [App\AnnotationsDiscovery::ANNOTATIONS_DISCO_CONSOLE],
                        'extensions' => ['php'],
                    ],
                    [
                        'folder' => dirname(__DIR__) . '/app/RouteHandler',
                        'flags' => [App\AnnotationsDiscovery::ANNOTATIONS_DISCO_ROUTE],
                        'extensions' => ['php'],
                    ],
                ],
                System\SetupContainer::CONFIG_SETUP_DEPENDENCIES => [
                    dirname(__DIR__) . '/config/dependencies.php',
                ],
                System\SetupEvents::CONFIG_SETUP_EVENTS => [
                    dirname(__DIR__) . '/config/events.php',
                ],
            ],
            // another config (partial), will be executed only once when caching is active, receives all config until that point
            static fn($config) => [
                'profile' => [
                    'setup' => false, // !$config['is_prod'],
                    'events' => false, // !$config['is_prod'],
                ],
            ],
            // another config (partial), contains the default `SatelliteApp` event service mapping
            // > dependencies/events in the configs are loaded before the `CONFIG_SETUP_*` files
            new Satellite\Launch\SatelliteModule(),
        )
        ->configure();
};
