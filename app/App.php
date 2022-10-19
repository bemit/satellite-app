<?php

namespace App;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Satellite\Event\Delegate;
use Satellite\KernelConsole;
use Satellite\Response\Request;
use Satellite\Response\ResponsePipe;
use Satellite\Launch\SatelliteAppInterface;

class App {
    protected ContainerInterface $container;
    protected InvokerInterface $invoker;
    protected EventDispatcherInterface $dispatcher;

    public function __construct(
        ContainerInterface       $container,
        InvokerInterface         $invoker,
        EventDispatcherInterface $dispatcher,
    ) {
        $this->container = $container;
        $this->invoker = $invoker;
        $this->dispatcher = $dispatcher;
    }

    public function launch(SatelliteAppInterface $app): Delegate {
        $delegate = new Delegate();
        $delegate->setEvent($app);

        if($app->isCLI()) {
            $delegate->setHandler([$this, 'handleConsole']);
            return $delegate;
        }

        $delegate->setHandler([$this, 'handleRoute']);
        return $delegate;
    }

    public function handleConsole(): void {
        /**
         * @var $console KernelConsole\Console
         */
        $console = $this->container->get(KernelConsole\Console::class);
        /**
         * @var $console KernelConsole\Console
         */
        $console = $this->dispatcher->dispatch($console);

        $command = $console->process();
        $handler = $command->getHandler();
        $this->invoker->call($handler, [$command]);
    }

    public function handleRoute(): void {
        /**
         * @var ResponsePipe $pipe
         */
        $pipe = $this->container->get(ResponsePipe::class);
        /**
         * @var ResponsePipe $pipe
         */
        $pipe = $this->dispatcher->dispatch($pipe);
        $response = $pipe->handle(Request::createContext());
        $pipe->emit($response);
    }
}
