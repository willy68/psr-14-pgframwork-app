<?php

namespace PgFramework\Middleware;

use GuzzleHttp\Psr7\Response;
use Invoker\CallableResolver;
use Mezzio\Router\RouteResult;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Invoker\Reflection\CallableReflection;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Invoker\ParameterResolver\ParameterResolver;

/**
 * Toute la magie est là
 *
 * Wrap $route callable controller in middleware
 */
class RouteCallerMiddleware implements MiddlewareInterface
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

        /** @var ParameterResolver $paramResolver */
        $paramResolver = $this->container->get(ParameterResolver::class);
        $callableReflection = CallableReflection::create($callback);
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
}
