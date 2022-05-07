<?php

namespace Tests\Framework\Middleware;

use GuzzleHttp\Psr7\Response;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Mezzio\Router\FastRouteRouter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Middleware\DispatcherMiddleware;

class DispatcherMiddlewareTest extends TestCase
{
    public function testDispatchTheCallback()
    {
        $callback = function () {
            return 'Hello';
        };
        $route = new Route('/demo', $callback);
        $routeResult = RouteResult::fromRoute($route);
        $router = $this->getMockBuilder(FastRouteRouter::class)->getMock();
        $request = (new ServerRequest('GET', '/demo'))->withAttribute(RouteResult::class, $routeResult);
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->method('get')->willReturn($router);
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response = new Response();

        $handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) use ($response) {
                return $response;
            }));
        /** @var ContainerInterface $container */
        $dispatcher = new DispatcherMiddleware($container);
        /** @var RequestHandlerInterface $handler */
        $this->assertEquals($response, $dispatcher->process($request, $handler));
    }

    public function testCallNextIfNotRoutes()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $delegate->expects($this->once())->method('handle')->willReturn($response);

        $request = (new ServerRequest('GET', '/demo'));
        /** @var ContainerInterface $container */
        $dispatcher = new DispatcherMiddleware($container);
        /** @var RequestHandlerInterface $delegate */
        $this->assertEquals($response, $dispatcher->process($request, $delegate));
    }

    public function testCallNextIfRoutesFailure()
    {
        $routeResult = RouteResult::fromRouteFailure(['GET']);
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $delegate
        ->expects($this->once())
        ->method('handle')
        ->will($this->returnCallback(function ($request) use ($response) {
            $routeResult = $request->getAttribute(RouteResult::class);
            $this->assertTrue($routeResult->isMethodFailure());
            return $response;
        }));

        $request = (new ServerRequest('GET', '/demo'))->withAttribute(RouteResult::class, $routeResult);
        /** @var ContainerInterface $container */
        $dispatcher = new DispatcherMiddleware($container);
        /** @var RequestHandlerInterface $delegate */
        $this->assertEquals($response, $dispatcher->process($request, $delegate));
    }
}
