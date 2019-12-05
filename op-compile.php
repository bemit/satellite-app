<?php
/*
 * Test File for op-cache compile, currently fails with more files
 */
require __DIR__ . '/vendor/autoload.php';

function searchFiles($dir) {
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

    $scanned_dir = [];
    foreach($iterator as $file) {
        if($file->isFile() && false !== strpos($file->getPathname(), 'test')) {
            continue;
        }
        if($file->isFile() && array_key_exists($file->getExtension(), ['php' => true])) {
            $scanned_dir[] = $file->getPathname();
        }
    }

    return $scanned_dir;
}

$dirs = [
    __DIR__ . '/app',
    __DIR__ . '/vendor/bin',
    //__DIR__ . '/vendor/composer',
    // adding doctrine is too much
    //__DIR__ . '/vendor/doctrine',
    __DIR__ . '/vendor/orbiter',
];

$scanned_files = [];
foreach($dirs as $dir) {
    array_push($scanned_files, ...searchFiles($dir));
}

$dirs_compile = [
    __DIR__ . '/tmp',
];

$scanned_files_compile = [
    __DIR__ . '/boot.php',
    __DIR__ . '/cli',
    __DIR__ . '/events.php',
    __DIR__ . '/launch.php',
    __DIR__ . '/server.php',
];
foreach($dirs_compile as $dir) {
    array_push($scanned_files_compile, ...searchFiles($dir));
}

foreach($scanned_files as $file) {
    require_once $file;
}

foreach($scanned_files_compile as $file) {
    opcache_compile_file($file);
}
