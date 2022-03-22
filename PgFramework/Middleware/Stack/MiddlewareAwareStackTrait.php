<?php

/**
 * https://github.com/thephpleague/route
 */

declare(strict_types=1);

namespace PgFramework\Middleware\Stack;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use PgFramework\Middleware\RoutePrefixMiddleware;

trait MiddlewareAwareStackTrait
{
    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * Add middleware
     *
     * @param string|MiddlewareInterface|callable $middleware
     * @return self
     */
    public function middleware($middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Add middlewares array
     *
     * @param string[]|MiddlewareInterface[]|callable[] $middlewares
     * @return self
     */
    public function middlewares(array $middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->middleware($middleware);
        }
        return $this;
    }

    /**
     * Add middleware in first
     *
     * @param string|MiddlewareInterface|callable $middleware
     * @return self
     */
    public function prependMiddleware($middleware): self
    {
        array_unshift($this->middleware, $middleware);
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $routePrefix
     * @param string|null $middleware
     * @param ContainerInterface $c
     * @return self
     */
    public function lazyPipe(ContainerInterface $c, string $routePrefix, ?string $middleware = null): self
    {
        $middleware = $middleware ?
            new RoutePrefixMiddleware($c, $routePrefix, $middleware) :
            $routePrefix;
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param ContainerInterface $c
     * @return mixed|MiddlewareInterface|null
     */
    public function shiftMiddleware(ContainerInterface $c)
    {
        $middleware =  array_shift($this->middleware);
        if ($middleware === null) {
            return null;
        }

        if (is_string($middleware)) {
            $middleware = $c->get($middleware);
        }
        return $middleware;
    }

    /**
     * get middleware stack
     *
     * @return iterable
     */
    public function getMiddlewareStack(): iterable
    {
        return $this->middleware;
    }
}
