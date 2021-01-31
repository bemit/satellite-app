<?php

namespace App\RouteHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Satellite\KernelRoute\Annotations\Get;
use Satellite\Response\Response;

/**
 * @Get("/api", name="api")
 */
class Api implements RequestHandlerInterface {
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        return (new Response([
            'success' => true,
            'path' => $request->getUri()->getPath(),
            'get_params' => $request->getQueryParams(),
        ]))->json();
    }
}
