<?php

date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

// if no env is set, pretend it is production
if(empty(getenv('env'))) {
    putenv('env=prod');
}

require_once __DIR__ . '/events.php';

require_once __DIR__ . '/launch.php';
