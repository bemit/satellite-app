<?php

return static function(
    \Satellite\Response\ResponsePipe $pipe,
    \Satellite\KernelRoute\Router $router,
    \Psr\Container\ContainerInterface $container
): void {
    $pipe->with((new Middlewares\JsonPayload())
        ->associative(false)
        ->depth(64));
    $pipe->with(new Middlewares\UrlEncodePayload());

    $pipe->with(new Middlewares\FastRoute($router->buildRouter()));

    $pipe->with(new Middlewares\RequestHandler($container));
};
