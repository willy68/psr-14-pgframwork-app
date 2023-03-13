<?php

namespace PgFramework\Middleware;

use Exception;
use GuzzleHttp\Psr7\Response;
use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use Invoker\ParameterResolver\ResolverChain;
use Mezzio\Router\RouteResult;
use PgFramework\Invoker\ParameterResolver\RequestParamResolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Invoker\Reflection\CallableReflection;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use ReflectionException;

/**
 * Toute la magie est là
 *
 * Wrap $route callable controller in middleware
 */
class RouteCallerMiddleware implements MiddlewareInterface
{
    protected RouteResult $result;

    protected ContainerInterface $container;

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
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws NotCallableException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws Exception
     */
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $callback = $this->result->getMatchedRoute()->getCallback();
        $params = $this->result->getMatchedParams();

        /** @var CallableResolver $callableResolver */
        $callableResolver = $this->container->get(CallableResolver::class);
        $callback = $callableResolver->resolve($callback);

        $paramsResolver = $this->container->get(ParameterResolver::class);
        $callableReflection = CallableReflection::create($callback);
        assert($paramsResolver instanceof ResolverChain);
        // Add request param resolver if needed (hint ServerRequestInterface)
        $paramsResolver->appendResolver(new RequestParamResolver($request));
        $params = $paramsResolver->getParameters($callableReflection, $params, []);

        $response = $callback(...$params);

        if (is_string($response)) {
            return new Response(200, [], $response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        } else {
            throw new Exception('The response is not a string or a ResponseInterface');
        }
    }
}
