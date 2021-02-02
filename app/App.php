<?php

namespace App;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Satellite\Event\Delegate;
use Satellite\KernelConsole;
use DI\Annotation\Inject;
use Satellite\Response\Request;
use Satellite\Response\ResponsePipe;
use Satellite\SatelliteAppInterface;

class App {
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected ContainerInterface $container;
    /**
     * @Inject
     * @var InvokerInterface
     */
    protected InvokerInterface $invoker;
    /**
     * @Inject
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $dispatcher;

    public function launch(SatelliteAppInterface $app) {
        $delegate = new Delegate();
        $delegate->setEvent($app);

        if($app->isCLI()) {
            $delegate->setHandler([$this, 'handleConsole']);
            return $delegate;
        }

        $delegate->setHandler([$this, 'handleRoute']);
        return $delegate;
    }

    public function handleConsole() {
        /**
         * @var $console KernelConsole\Console
         */
        $console = $this->container->get(KernelConsole\Console::class);
        $console = $this->dispatcher->dispatch($console);

        $command = $console->process();
        $handler = $command->getHandler();
        $this->invoker->call($handler, [$command]);
    }

    public function handleRoute() {
        /**
         * @var $pipe ResponsePipe
         */
        $pipe = $this->container->get(ResponsePipe::class);
        $pipe = $this->dispatcher->dispatch($pipe);
        $response = $pipe->handle(Request::createContext());
        $pipe->emit($response);
    }
}
