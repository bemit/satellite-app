<?php declare(strict_types=1);

date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

return static function(): \Psr\Container\ContainerInterface {
    $config = (require __DIR__ . '/config/config.php')();

    if(isset($config['whoops']) && $config['whoops']) {
        Satellite\Whoops\NiceDebug::enable(PHP_SAPI === 'cli', $_ENV['dev.editor'] ?? null);
    }

    return Satellite\System\SystemControl::fromConfig(
        $config,
        // system setup step profiling requires manual Profiler setup, as `dependencies` are not available yet here
            $config['profile']['setup'] ?? false ?
            new \Satellite\EventProfiler\EventProfiler(new \Satellite\EventProfiler\EventProfilerReporterLog()) : null,
    );
};

