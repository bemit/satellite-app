<?php
/*
 * Test File for op-cache compile, currently fails with more files
 */
require __DIR__ . '/vendor/autoload.php';

function searchFiles($dir) {
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);

    $scanned_dir = [];
    foreach($iterator as $file) {
        /**
         * @var SplFileInfo $file
         */
        if(
            !$file->isFile() ||
            $file->getExtension() !== 'php' ||
            str_contains($file->getPathname(), 'test') ||
            str_starts_with($file->getBasename(), '.')
        ) {
            continue;
        }
        $scanned_dir[] = $file->getPathname();
    }

    return $scanned_dir;
}

$dirs = [
    __DIR__ . '/app',
    __DIR__ . '/vendor/bin',
    __DIR__ . '/vendor/composer',
    __DIR__ . '/vendor/league/flysystem/src',
    __DIR__ . '/vendor/monolog/monolog/src',
    __DIR__ . '/vendor/nyholm/psr7/src',
    __DIR__ . '/vendor/orbiter',
    __DIR__ . '/vendor/php-di/invoker/src',
    __DIR__ . '/vendor/psr',
    __DIR__ . '/vendor/scaleupstack',
    __DIR__ . '/vendor/vlucas/phpdotenv/src',
];

$scanned_files = [];
foreach($dirs as $dir) {
    array_push($scanned_files, ...searchFiles($dir));
}

$dirs_compile = [
];

$scanned_files_compile = [
    __DIR__ . '/assemble.php',
    __DIR__ . '/cli',
    __DIR__ . '/launch.php',
    __DIR__ . '/web/index.php',
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
