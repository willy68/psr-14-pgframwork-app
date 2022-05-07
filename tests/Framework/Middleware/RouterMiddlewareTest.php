<?php

namespace Tests\Framework\Middleware;

use PgFramework\Middleware\RouterMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddlewareTest extends TestCase
{
    private $handler;

    public function setUp(): void
    {
        $this->handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    public function makeMiddleware($routeResult)
    {
        $router = $this->getMockBuilder(RouterInterface::class)->getMock();
        $router->method('match')->willReturn($routeResult);
        /** @var RouterInterface $router */
        return new RouterMiddleware($router);
    }

    public function testPassParameters()
    {
        $callback = function () {
            return 'Hello';
        };
        $route = new Route('/demo-{id:\d+}', $callback);
        $routeResult = RouteResult::fromRoute($route, ['id' => 2]);
        $middleware = $this->makeMiddleware($routeResult);
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function (ServerRequestInterface $request) use ($route) {
                $routeResult = $request->getAttribute(RouteResult::class);
                $this->assertEquals($route, $routeResult->getMatchedRoute());
                $this->assertEquals(2, $request->getAttribute('id'));
                return new Response();
            }));
        $middleware->process(new ServerRequest('GET', '/demo-2'), $this->handler);
    }

    public function testCallNext()
    {
        $routeResult = RouteResult::fromRouteFailure(Route::HTTP_METHOD_ANY);
        $middleware = $this->makeMiddleware($routeResult);
        $response = new Response();
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) use ($response) {
                return $response;
            }));
        $r = $middleware->process(new ServerRequest('GET', '/demo'), $this->handler);
        $this->assertEquals($response, $r);
    }

    public function testWithMethodFailure()
    {
        $routeResult = RouteResult::fromRouteFailure(['POST']);
        $middleware = $this->makeMiddleware($routeResult);
        $response = new Response();
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function ($request) use ($response) {
                $routeResult = $request->getAttribute(RouteResult::class);
                $this->assertTrue($routeResult->isMethodFailure());
                return $response;
            }));
        $r = $middleware->process(new ServerRequest('GET', '/demo-2'), $this->handler);
        $this->assertEquals($response, $r);
    }
}
