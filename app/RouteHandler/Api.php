<?php

namespace App\RouteHandler;

use DI\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Satellite\KernelRoute\Annotations\Post;
use Satellite\Response\Respond;

class Api {
    /**
     * @Inject
     * @var \Satellite\KernelRoute\RouteEvent
     */
    protected $event;

    /**
     * @Post("/api/auth", name="api")
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @throws \Exception
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handleApiAuth(ServerRequestInterface $request, RequestHandlerInterface $handler) {
        return Respond::json(['success' => true], $request, $handler);
    }
}
