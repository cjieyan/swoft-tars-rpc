<?php

namespace Swoft\TarsRpc\Server\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;

/**
 * service handler adapter
 *
 * @Bean()
 */
class HandlerAdapterMiddleware implements MiddlewareInterface
{
    /**
     * execute service with handler
     *
     * @param \Psr\Http\Message\ServerRequestInterface     $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serviceHandler = $request->getAttribute(RouterMiddleware::ATTRIBUTE);

        /* @var \Swoft\TarsRpc\Server\Router\HandlerAdapter $handlerAdapter */
        $handlerAdapter = App::getBean('serviceHandlerAdapter');
        $response       = $handlerAdapter->doHandler($request, $serviceHandler);

        return $response;
    }
}
