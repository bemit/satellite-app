<?php

// PHP-dev server file requests handling
if(PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

if(PHP_SAPI === 'cli-server' || $_ENV['env'] !== 'prod') {
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

require_once __DIR__ . '/../launch.php';

if(PHP_SAPI === 'cli-server' || $_ENV['env'] !== 'prod') {
    error_log('... ' . number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 6) . 's' . PHP_EOL);
}
