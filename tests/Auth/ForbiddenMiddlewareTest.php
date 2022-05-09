<?php

namespace Tests\App\Auth;

use Mezzio\Session\SessionInterface;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Auth\ForbiddenException;
use PgFramework\Auth\Middleware\ForbidenMiddleware;
use PgFramework\Auth\UserInterface;
use PgFramework\Session\ArraySession;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

class ForbiddenMiddlewareTest extends TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function setUp(): void
    {
        $this->session = new ArraySession();
    }

    public function makeRequest($path = '/')
    {
        $uri = $this->getMockBuilder(UriInterface::class)->getMock();
        $uri->method('getPath')->willReturn($path);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getUri')->willReturn($uri);
        return $request;
    }

    public function makeDelegate()
    {
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        return $delegate;
    }

    public function makeMiddleware()
    {
        $session = $this->session;
        /** @var SessionInterface $session */
        return new ForbidenMiddleware('/login', $session);
    }

    public function testCatchForbiddenException()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $delegate->expects($this->once())->method('handle')->willThrowException(new ForbiddenException());
        /** @var ServerRequestInterface $request */
        /** @var RequestHandlerInterface $delegate*/
        $response = $this->makeMiddleware()->process($request, $delegate);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testCatchForbiddenExceptionJson()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('application/json');
        $delegate->expects($this->once())->method('handle')->willThrowException(new ForbiddenException());
        /** @var ServerRequestInterface $request */
        /** @var RequestHandlerInterface $delegate*/
        $response = $this->makeMiddleware()->process($request, $delegate);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testCatchFailedAccessException()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getServerParams')->willReturn(['HTTP_REFERER' => '/test']);
        $delegate->expects($this->once())->method('handle')->willThrowException(new FailedAccessException());
        /** @var ServerRequestInterface $request */
        /** @var RequestHandlerInterface $delegate*/
        $response = $this->makeMiddleware()->process($request, $delegate);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/test'], $response->getHeader('Location'));
    }

    public function testCatchFailedAccessExceptionJson()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('application/json');
        $delegate->expects($this->once())->method('handle')->willThrowException(new FailedAccessException());
        /** @var ServerRequestInterface $request */
        /** @var RequestHandlerInterface $delegate*/
        $response = $this->makeMiddleware()->process($request, $delegate);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testCatchTypeErrorException()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $delegate->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new \TypeError(UserInterface::class, 200);
        });
        /** @var ServerRequestInterface $request */
        /** @var RequestHandlerInterface $delegate*/
        $response = $this->makeMiddleware()->process($request, $delegate);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testBubbleError()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $delegate->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new \TypeError("test", 200);
        });
        try {
            /** @var ServerRequestInterface $request */
            /** @var RequestHandlerInterface $delegate*/
            $this->makeMiddleware()->process($request, $delegate);
        } catch (\TypeError $e) {
            $this->assertEquals("test", $e->getMessage());
            $this->assertEquals(200, $e->getCode());
        }
    }

    public function testTypeErrorExceptionJson()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('application/json');
        $delegate->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new \TypeError(UserInterface::class, 200);
        });
        try {
            /** @var ServerRequestInterface $request */
            /** @var RequestHandlerInterface $delegate*/
            $response = $this->makeMiddleware()->process($request, $delegate);
        } catch (\TypeError $e) {
            $this->assertEquals("test", $e->getMessage());
            $this->assertEquals(200, $e->getCode());
        }
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testProcessValidRequest()
    {
        $delegate = $this->makeDelegate();
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        /** @var ServerRequestInterface $request */
        /** @var RequestHandlerInterface $delegate*/
        $this->assertSame($response, $this->makeMiddleware()->process($request, $delegate));
    }
}
