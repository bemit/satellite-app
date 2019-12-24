<?php

namespace App\Commands;

use Satellite\KernelConsole\Annotations\Command;
use Satellite\KernelConsole\Annotations\CommandOption;
use Satellite\KernelConsole\Annotations\CommandOperand;

class DemoMultiple {
    /**
     * @Command(
     *     name="demo:welcome",
     *     options={
     *          @CommandOption(long="formal", description="if formal welcome or simple", default=false)
     *     },
     *     operands={
     *          @CommandOperand(name="name", description="the name to welcome")
     *     }
     * )
     */
    public function handleWelcome(\Satellite\KernelConsole\ConsoleEvent $console) {
        error_log(($console->getOptions()['formal'] ? 'Hello ' : 'Hi ') . (isset($console->getOperands()[0]) ? $console->getOperands()[0] : 'there') . '!');
    }

    /**
     * @Command(
     *     name="demo:bye",
     *     operands={
     *          @CommandOperand(name="name", description="the name to welcome")
     *     }
     * )
     */
    public function handleBye(\Satellite\KernelConsole\ConsoleEvent $console) {
        error_log('Bye ' . (isset($console->getOperands()[0]) ? $console->getOperands()[0] : 'there') . '!');
    }
}
