<?php
namespace Tests\Framework\Middleware;

use Framework\Middleware\NotFoundMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundMiddlewareTest extends TestCase
{

    public function testSendNotFound()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $delegate->expects($this->never())->method('handle')->willReturn($response);
        $request = (new ServerRequest('GET', '/demo'));
        $middleware = new NotFoundMiddleware();
        $response = $middleware->process($request, $delegate);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
