<?php declare(strict_types=1);

(static function() {
    // PHP-dev server file requests handling
    if(PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
        return false;
    }

    // this variable is set before `.env` is loaded, even before auto-loading,
    // thus must be controlled by system (or e.g. docker)
    $log_perf_or_not_prod = (
        PHP_SAPI === 'cli-server' ||
        (isset($_ENV['env']) && $_ENV['env'] !== 'prod') ||
        (
            isset($_ENV['satellite_index_log_perf']) && (
                $_ENV['satellite_index_log_perf'] === 'on' ||
                $_ENV['satellite_index_log_perf'] === '1' ||
                $_ENV['satellite_index_log_perf'] === true ||
                $_ENV['satellite_index_log_perf'] === 'yes'
            )
        )
    );

    if($log_perf_or_not_prod) {
        $ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        $port = $_SERVER['SERVER_PORT'];
        $port = ((!$ssl && $port === '80') || ($ssl && $port === '443')) ? '' : ':' . $port;
        error_log(
            $_SERVER['REQUEST_METHOD'] . ':http' . ($ssl ? 's' : '') . '://' . (
                $_SERVER['HTTP_X_FORWARDED_HOST'] ??
                ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] . $port)
            ) . $_SERVER['REQUEST_URI']
        );
    }

    // the actual start code:
    require_once dirname(__DIR__) . '/launch.php';

    if($log_perf_or_not_prod) {
        error_log('... ' . number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 6) . 's' . PHP_EOL);
    }

    return true;
})();
