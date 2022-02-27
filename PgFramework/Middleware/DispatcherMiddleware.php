<?php

namespace PgFramework\Middleware;

use Invoker\Invoker;
use GuzzleHttp\Psr7\Response;
use Invoker\InvokerInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;

/**
 * Undocumented class
 */
class DispatcherMiddleware implements MiddlewareInterface, RequestHandlerInterface
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
     * Next App Handler
     *
     * @var RequestHandlerInterface
     */
    private $next;

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

        $this->next = $next;
        $this->router = $this->container->get(RouterInterface::class);
        $this->prepareMiddlewareStack($this->router, $result);

        return $this->handle($request);
    }

    /**
     * Internal routing Middleware handler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->router->shiftMiddleware($this->container);

        if (is_null($middleware)) {
            // return App handler;
            return $this->next->handle($request);
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request, [$this, 'handle']]);
        }
        return $this->next->handle($request);
    }

    /**
     * Prepare la pile de middleware du router
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
     * Wrap$route callable controller in middleware
     *
     * @param RouteResult $route
     * @param ContainerInterface $container
     * @param Invoker $invoker
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
             * Invoker des callback des$route
             *
             * @var Invoker
             */
            protected $invoker;

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

                $response = $this->getInvoker($this->container)->call($callback, $params);

                if (is_string($response)) {
                    return new Response(200, [], $response);
                } elseif ($response instanceof ResponseInterface) {
                    return $response;
                } else {
                    throw new \Exception('The response is not a string or a ResponseInterface');
                }
                return $requestHandler->handle($request);
            }

            /**
             * crée un Invoker
             *
             * @param \Psr\Container\ContainerInterface $container
             * @return InvokerInterface
             */
            protected function getInvoker(ContainerInterface $container): InvokerInterface
            {
                if (!$this->invoker) {
                    $parameterResolver = new ResolverChain([
                    new ActiveRecordAnnotationsResolver(),
                    new ActiveRecordResolver(),
                    new NumericArrayResolver(),
                    new AssociativeArrayResolver(),
                    new DefaultValueResolver(),
                    new TypeHintContainerResolver($container)
                    ]);
                    $this->invoker = new Invoker($parameterResolver, $container);
                }
                return $this->invoker;
            }
        };
    }
}
