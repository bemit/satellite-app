<?php declare(strict_types=1);

return static function(
    \Satellite\Response\ResponsePipe  $pipe,
    \Satellite\KernelRoute\Router     $router,
    \Psr\Container\ContainerInterface $container
): \Satellite\Response\ResponsePipe {
    $pipe->with((new Middlewares\JsonPayload())
        ->associative(false)
        ->depth(64));
    $pipe->with(new Middlewares\UrlEncodePayload());

    $pipe->with(new Middlewares\FastRoute($router->buildRouter()));

    $pipe->with(new Middlewares\RequestHandler($container));

    return $pipe;
};
