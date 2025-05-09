<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use PgRouter\Route;
use PgRouter\Router;
use PgRouter\RouteGroup;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;

    private Router $router;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Psr15 middleware process method
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $result */
        $result = $request->getAttribute(RouteResult::class);
        if (is_null($result)) {
            return $handler->handle($request);
        }
        if ($result->isMethodFailure()) {
            return $handler->handle($request);
        }

        $this->router = $this->container->get(RouterInterface::class);
        $this->prepareMiddlewareStack($result);

        $middleware = new CombinedMiddleware($this->container, (array)$this->router->getMiddlewareStack());
        return $middleware->process($request, $handler);
    }

    /**
     * Prepare la pile de middlewares du router
     *
     * @param RouteResult $result
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function prepareMiddlewareStack(RouteResult $result): void
    {
        /** @var Route $route */
        if ($route = $result->getMatchedRoute()) {
            // router stack first
            if ($this->container->has('router.middlewares')) {
                $this->router->middlewares($this->container->get('router.middlewares'));
            }
            /** @var RouteGroup $group */
            if ($group = $route->getParentGroup()) {
                foreach ($group->getMiddlewareStack() as $middleware) {
                    $this->router->middleware($middleware);
                }
            }
            // $route stack
            foreach ($route->getMiddlewareStack() as $middleware) {
                $this->router->middleware($middleware);
            }
            // wrap $route callable end
            $this->router->middleware(new RouteCallerMiddleware($result, $this->container));
        }
    }
}
