<?php
namespace Tests\Framework\Middleware;

use Framework\Middleware\NotFoundMiddleware;
use Framework\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlashMiddlewareTest extends TestCase
{

    /**
     * @var RequestHandlerInterface
     */
    private $handler;

    public function setUp(): void
    {
        $this->handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
    }

    public function testRedirectIfSlash()
    {
        $request = (new ServerRequest('GET', '/demo/'));
        $middleware = new TrailingSlashMiddleware();
        $this->handler
            ->expects($this->never())
            ->method('handle')
            ->will($this->returnCallback(function ($request) {
            }));
        $response = $middleware->process($request, $this->handler);
        $this->assertEquals(['/demo'], $response->getHeader('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testCallNextIfNoSlash()
    {
        $request = (new ServerRequest('GET', '/demo'));
        $response = new Response();
        $middleware = new TrailingSlashMiddleware();
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function () use ($response) {
                return $response;
            }));
        $this->assertEquals($response, $middleware->process($request, $this->handler));
    }
}
