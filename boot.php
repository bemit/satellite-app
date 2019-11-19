<?php

date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$namespaces = [
    'App\\Command\\' => 'Command',
    'App\\Action\\' => 'Action',
    'App\\Store\\' => 'Store',
    'App\\Controller\\' => 'Controller',
    'App\\Service\\' => 'Service',
];

$registerNamespace = static function($prefix, $path) {
    spl_autoload_register(static function($class) use ($prefix, $path) {
        $prefix_length = strlen($prefix);
        if(strncmp($prefix, $class, $prefix_length) !== 0) {
            return;
        }
        $src_path = $path . '/';
        $class_path = __DIR__ . '/' . $src_path . str_replace('\\', '/', substr($class, $prefix_length)) . '.php';
        if(file_exists($class_path)) {
            require_once $class_path;
        }
    });
};

foreach($namespaces as $ns => $path) {
    $registerNamespace($ns, $path);
}

require_once __DIR__ . '/_config.php';
