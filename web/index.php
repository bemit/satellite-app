<?php

if(PHP_SAPI === 'cli-server' || getenv('env') !== 'prod') {
    $ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    $port = $_SERVER['SERVER_PORT'];
    $port = ((!$ssl && $port === '80') || ($ssl && $port === '443')) ? '' : ':' . $port;
    error_log(
        $_SERVER['REQUEST_METHOD'] . ':http' . ($ssl ? 's' : '') . '://' . (
        isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
            $_SERVER['HTTP_X_FORWARDED_HOST'] :
            (isset($_SERVER['HTTP_HOST']) ?
                $_SERVER['HTTP_HOST'] :
                $_SERVER['SERVER_NAME'] . $port)
        ) . $_SERVER['REQUEST_URI']
    );
}

require_once __DIR__ . '/../boot.php';

require_once __DIR__ . '/../exec.php';

if(PHP_SAPI === 'cli-server' || getenv('env') !== 'prod') {
    error_log('... ' . number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 6) . 's' . PHP_EOL);
}
