<?php

namespace Tests\Framework;

use PgFramework\App;
use PgFramework\Kernel\KernelMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class AppTest extends TestCase
{
    /**
     * @var App
     */
    private $app;

    public function setUp(): void
    {
        $this->app = new App();
    }

    public function testApp()
    {
        $this->app->addModule(get_class($this));
        $this->assertEquals([get_class($this)], $this->app->getModules());
    }

    public function testGetKernelWithMiddleware()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);
        $this->app->addMiddleware($middleware);
        $this->app->run();
        $this->assertInstanceOf(KernelMiddleware::class, $this->app->getKernel());
    }

    public function testPipe()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $middleware2 = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);
        $middleware2->expects($this->never())->method('process')->willReturn($response);
        $this->assertEquals($response, $this->app->addMiddlewares([$middleware,$middleware2])->run());
    }

    public function testPipeWithClosure()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);
        $this->app
            ->addMiddleware(function ($request, $next) {
                return $next($request);
            })
            ->addMiddleware($middleware);
        $this->assertEquals($response, $this->app->run());
    }

    public function testPipeWithoutMiddleware()
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $this->expectException(\Exception::class);
        $this->app->run();
    }
/*
    public function testPipeWithPrefix()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);

        $this->app->pipe('/demo', $middleware);
        $this->assertEquals($response, $this->app->run(new ServerRequest('GET', '/demo/hello')));
        $this->expectException(\Exception::class);
        $this->assertEquals($response, $this->app->run(new ServerRequest('GET', '/hello')));
    }
    */
}
