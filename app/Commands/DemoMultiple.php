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
    public function handleWelcome(\GetOpt\Command $command) {
        error_log(($command->getOptions()['formal'] ? 'Hello ' : 'Hi ') . (isset($command->getOperands()[0]) ? $command->getOperands()[0] : 'there') . '!');
    }

    /**
     * @Command(
     *     name="demo:bye",
     *     operands={
     *          @CommandOperand(name="name", description="the name to welcome")
     *     }
     * )
     */
    public function handleBye(\GetOpt\Command $command) {
        error_log('Bye ' . (isset($command->getOperands()[0]) ? $command->getOperands()[0] : 'there') . '!');
    }
}
