<?php

namespace Tests\Framework\Middleware;

use PgFramework\Middleware\PageNotFoundMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PgFramework\Renderer\RendererInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundMiddlewareTest extends TestCase
{
    public function testSendNotFound()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
        $delegate->expects($this->never())->method('handle')->willReturn($response);
        $request = (new ServerRequest('GET', '/demo'));
        /** @var RendererInterface $renderer */
        $middleware = new PageNotFoundMiddleware($renderer);
        /** @var RequestHandlerInterface $delegate */
        $response = $middleware->process($request, $delegate);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
