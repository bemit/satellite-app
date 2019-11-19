<?php

use Satellite\KernelConsole\Command;

Command::create('version', static function() {
    error_log('v0.0.1 ' . PHP_EOL .
        '_,.-\'´(_;,.-\'´(_,.-\'´(_,.-\'´(_,.-\'´(_,.-\'´(_,.-\'´('
    );
});
