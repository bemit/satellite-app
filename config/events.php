<?php

use Satellite\Event\EventListenerInterface;

return static function(
    EventListenerInterface $event,
    Satellite\SatelliteAppInterface $app
): void {
    $satellite_app = get_class($app);
    $event->on($satellite_app, [App\AnnotationsDiscovery::class, 'discover']);
    $event->on($satellite_app, [App\App::class, 'launch']);

    $event->on(Satellite\KernelConsole\Console::class, [App\AnnotationsDiscovery::class, 'bindCommands']);
    $event->on(Satellite\KernelConsole\Console::class, static function(Satellite\KernelConsole\Console $console, \GetOpt\GetOpt $get_opt) {
        $cmd = new GetOpt\Command('demo2', function(GetOpt\Command $command) {
            error_log('Hi ' . (isset($command->getOperands()[0]) ? $command->getOperands()[0]->getValue() : 'there') . '!');
        });
        $cmd->addOperand(new GetOpt\Operand('name'));
        $get_opt->addCommand($cmd);
        return $console;
    });

    $event->on(Satellite\Response\ResponsePipe::class, [App\AnnotationsDiscovery::class, 'bindRoutes']);
    $event->on(
        Satellite\Response\ResponsePipe::class,
        static function(Satellite\Response\ResponsePipe $pipe, Invoker\InvokerInterface $invoker) {
            $pipeline = require __DIR__ . '/../config/pipeline.php';
            $invoker->call($pipeline);

            return $pipe;
        }
    );
};
