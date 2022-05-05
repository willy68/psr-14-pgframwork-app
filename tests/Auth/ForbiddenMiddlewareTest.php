<?php
namespace Tests\App\Auth;

use App\Auth\ForbiddenMiddleware;
use Framework\Auth\ForbiddenException;
use Framework\Auth\User;
use Framework\Session\ArraySession;
use Framework\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
        return new ForbiddenMiddleware('/login', $this->session);
    }

    public function testCatchForbiddenException()
    {
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('handle')->willThrowException(new ForbiddenException());
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $delegate);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testCatchTypeErrorException()
    {
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('handle')->willReturnCallback(function (User $user) {
            return true;
        });
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $delegate);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testBubbleError()
    {
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new \TypeError("test", 200);
        });
        try {
            $this->makeMiddleware()->process($this->makeRequest('/test'), $delegate);
        } catch (\TypeError $e) {
            $this->assertEquals("test", $e->getMessage());
            $this->assertEquals(200, $e->getCode());
        }
    }

    public function testProcessValidRequest()
    {
        $delegate = $this->makeDelegate();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);
        $this->assertSame($response, $this->makeMiddleware()->process($this->makeRequest('/test'), $delegate));
    }
}
