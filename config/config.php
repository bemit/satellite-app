<?php

return static function() {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // if no env is set, pretend it is production
    if(!isset($_ENV['env']) || empty($_ENV['env'])) {
        $_ENV['env'] = 'prod';
    }

    return [
        'dir_tmp' => __DIR__ . '/../tmp',
        'annotation' => [
            // Folders containing annotations
            'psr4' => [
                // PSR4\Namespace => abs/Path
                'Satellite\KernelConsole\Annotations' => __DIR__ . '/../vendor/orbiter/satellite-console/src/Annotations',
                'Satellite\KernelRoute\Annotations' => __DIR__ . '/../vendor/orbiter/satellite-route/src/Annotations',
            ],
            // annotations to ignore, Doctrine\Annotations applies a default filter
            'ignore' => [
                'dummy',
            ],
        ],
        'code_info' => [
            // Folders searched for infos to be used in annotation discovery
            App\AnnotationsDiscovery::ANNOTATIONS_DISCOVERY => [
                __DIR__ . '/../app/Commands',
                __DIR__ . '/../app/RouteHandler',
            ],
        ],
    ];
};
