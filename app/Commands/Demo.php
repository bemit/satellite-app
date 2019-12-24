<?php

namespace App\Commands;

use Satellite\KernelConsole\Annotations\Command;
use Satellite\KernelConsole\Annotations\CommandOperand;

/**
 * Class Demo
 *
 * @Command(
 *     name="demo",
 *     handler="handle",
 *     operands={
 *          @CommandOperand(name="name", description="the name to welcome")
 *     }
 * )
 */
class Demo {
    public function handle(\Satellite\KernelConsole\ConsoleEvent $console) {
        error_log('Hi ' . (isset($console->getOperands()[0]) ? $console->getOperands()[0] : 'there') . '!');
    }
}
