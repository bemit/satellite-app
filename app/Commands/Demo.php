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
    public function handle(\GetOpt\Command $command) {
        error_log('Hi ' . (isset($command->getOperands()[0]) ? $command->getOperands()[0] : 'there') . '!');
    }
}
