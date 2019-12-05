<?php

namespace App\Commands;

use Satellite\KernelConsole\Annotations\Command;

class OpCache {
    protected function arrayPrinter($arr, $prefix = '') {
        $max_len = 0;
        foreach($arr as $lbl => $val) {
            if(!is_array($val)) {
                if($max_len < strlen($lbl)) {
                    $max_len = strlen($lbl);
                }
            }
        }
        foreach($arr as $lbl => $val) {
            $lbl = ucwords(str_replace('_', ' ', $lbl));
            if(is_array($val)) {
                fwrite(STDOUT, $prefix . $lbl . PHP_EOL);
                $this->arrayPrinter($val, '    ');
            } else {
                if(is_bool($val)) {
                    $val = $val ? 'y' : 'n';
                }
                fwrite(STDOUT, $prefix . $lbl . ' ' . implode('', array_fill(0, $max_len - strlen($lbl) + 1, '.')) . ' ' . $val . PHP_EOL);
            }
        }
    }

    /**
     * @Command("opcache:status")
     */
    public function handleStatus() {
        if(!function_exists('opcache_get_status')) {
            fwrite(STDOUT, 'No OpCache available.' . PHP_EOL);
            return;
        }
        $status = opcache_get_status();
        if(!$status) {
            fwrite(STDOUT, 'OpCache turned-off.' . PHP_EOL);
            return;
        }

        $this->arrayPrinter($status);
    }

    /**
     * @Command("opcache:reset")
     * @package Commands
     */
    public function handleReset() {
        opcache_reset();
    }
}
