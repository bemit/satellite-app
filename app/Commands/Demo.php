<?php

namespace App\Commands;

use Satellite\KernelConsole\Annotations\Command;
use Satellite\KernelConsole\Annotations\CommandOperand;
use DI\Annotation\Inject;

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

    /**
     * this property uses the `Inject` annotation from `php-di` and get's the ConsoleApp injected
     *
     * @Inject
     * @var \Satellite\KernelConsole\ConsoleEvent $console
     */
    protected $console;

    public function handle() {
        error_log('Hi ' . (isset($this->console->getOperands()[0]) ? $this->console->getOperands()[0] : 'there') . '!');
    }
}
