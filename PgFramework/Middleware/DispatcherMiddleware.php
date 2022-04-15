<?php

declare(strict_types=1);

namespace PgFramework\Middleware;

use GuzzleHttp\Psr7\Response;
use Invoker\CallableResolver;
use Mezzio\Router\RouteResult;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Invoker\Reflection\CallableReflection;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Invoker\ParameterResolver\ParameterResolver;

/**
 * Toute la magie est là
 */
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
     *
     *
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
        $this->prepareMiddlewareStack($this->router, $result);

        return (new CombinedMiddleware($this->container, $this->router->getMiddlewareStack(), $next))
            ->process($request, $next);
    }

    /**
     * Prepare la pile de middlewares du router
     *
     * @param Router $router
     * @param Route|null $route
     * @return void
     */
    protected function prepareMiddlewareStack(FastRouteRouter $router, ?RouteResult $result): void
    {
        if ($route = $result->getMatchedRoute()) {
            /** router stack first */
            if ($this->container->has('router.middlewares')) {
                $router->middlewares($this->container->get('router.middlewares'));
            }
            /** @var Route $route */
            if ($group = $route->getParentGroup()) {
                foreach ($group->getMiddlewareStack() as $middleware) {
                    $router->middleware($middleware);
                }
            }
            /**$route stack */
            foreach ($route->getMiddlewareStack() as $middleware) {
                $router->middleware($middleware);
            }
            /** wrap$route callable end */
            $router->middleware($this->routeCallableMiddleware($result, $this->container));
        }
    }

    /**
     * Wrap $route callable controller in middleware
     *
     * @param RouteResult $route
     * @param ContainerInterface $container
     * @return MiddlewareInterface
     */
    protected function routeCallableMiddleware(
        RouteResult $result,
        ContainerInterface $container
    ): MiddlewareInterface {
        return new class ($result, $container) implements MiddlewareInterface
        {
            /**
             * Route result
             *
             * @var RouteResult
             */
            protected $result;

            /**
             * ContainerInterface
             *
             * @var ContainerInterface
             */
            protected $container;

            /**
             *  constructor.
             * @param RouteResult $result
             * @param ContainerInterface $container
             */
            public function __construct(RouteResult $result, ContainerInterface $container)
            {
                $this->result = $result;
                $this->container = $container;
            }

            /**
             * Psr15 middleware process method
             * @param ServerRequestInterface $request
             * @param RequestHandlerInterface $requestHandler
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {

                $callback = $this->result->getMatchedRoute()->getCallback();
                $params = $this->result->getMatchedParams();

                if ($this->container instanceof \DI\Container) {
                    $this->container->set(ServerRequestInterface::class, $request);
                } else {
                    // Limitation: $request must be named "$request"
                    $params = array_merge(["request" => $request], $params);
                }

                /** @var CallableResolver */
                $callableResolver = $this->container->get(CallableResolver::class);
                $callback = $callableResolver->resolve($callback);
                $callableReflection = CallableReflection::create($callback);

                /** @var ParameterResolver $paramResolver */
                $paramResolver = $this->container->get(ParameterResolver::class);
                $params = $paramResolver->getParameters($callableReflection, $params, []);

                $response = $callback(...$params);

                if (is_string($response)) {
                    return new Response(200, [], $response);
                } elseif ($response instanceof ResponseInterface) {
                    return $response;
                } else {
                    throw new \Exception('The response is not a string or a ResponseInterface');
                }
                return $requestHandler->handle($request);
            }
        };
    }
}
