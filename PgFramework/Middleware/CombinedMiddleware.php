<?php

namespace PgFramework\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Middleware\Stack\MiddlewareAwareStackTrait;

class CombinedMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    use MiddlewareAwareStackTrait;

    private ContainerInterface $container;

    private RequestHandlerInterface $handler;

    public function __construct(ContainerInterface $container, array $middlewares)
    {
        $this->middlewares = $middlewares;
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->handler = $handler;
        return $this->handle($request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->shiftMiddleware($this->container);
        if (is_null($middleware)) {
            return $this->handler->handle($request);
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request, [$this, 'handle']]);
        }
        return $this->handler->handle($request);
    }
}
