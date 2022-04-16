<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use Mezzio\Router\Route;
use Mezzio\Router\RouteGroup;
use Mezzio\Router\RouteResult;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    /**
     * Injection container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Router
     *
     * @var FastRouteRouter
     */
    private $router;

    /**
     * @param ContainerInterface $container
     * @param Invoker $invoker
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Psr15 middleware process method
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        /** @var RouteResult $result */
        $result = $request->getAttribute(RouteResult::class);
        if (is_null($result)) {
            return $next->handle($request);
        }
        if ($result->isMethodFailure()) {
            return $next->handle($request);
        }

        $this->router = $this->container->get(RouterInterface::class);
        $this->prepareMiddlewareStack($result);

        return (new CombinedMiddleware($this->container, $this->router->getMiddlewareStack(), $next))
            ->process($request, $next);
    }

    /**
     * Prepare la pile de middlewares du router
     *
     * @param RouteResult $result
     * @return void
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
