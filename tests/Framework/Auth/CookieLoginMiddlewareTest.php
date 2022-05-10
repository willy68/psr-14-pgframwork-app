<?php

namespace Tests\Framework\Auth;

use PgFramework\Auth;
use GuzzleHttp\Psr7\ServerRequest;
use PgFramework\Auth\Middleware\CookieLoginMiddleware;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CookieLoginMiddlewareTest extends TestCase
{
    private $cookie;

    private $auth;

    public function setUp(): void
    {
        $this->cookie = $this->getMockBuilder(RememberMeInterface::class)->getMock();
        $this->auth = $this->getMockBuilder(Auth::class)->getMock();
    }

    public function makeMiddleware($user)
    {
        $this->auth->method('getUser')->willReturn($user);
        /** @var Auth $auth */
        return new CookieLoginMiddleware($this->auth, $this->cookie);
    }

    public function makeDelegate($calls)
    {
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate->expects($calls)->method('handle')->willReturn($response);
        /** @var RequestHandlerInterface $delegate */
        return $delegate;
    }

    public function testThrowIfNoUserLoggedAndNoUserInRequestAttribute()
    {
        $request = new ServerRequest('GET', '/demo/');
        $this->cookie->method('autoLogin')->willReturn($request);
        $this->expectException(Auth\ForbiddenException::class);
        $this->makeMiddleware(null)->process(
            $request,
            $this->makeDelegate($this->never())
        );
    }

    public function testThrowIfNoUserLoggedAndUserInRequestAttribute()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $request = new ServerRequest('GET', '/demo/');
        $this->cookie->method('autoLogin')->willReturn($request->withAttribute('_user', $user));

        $this->makeMiddleware(null)->process(
            $request,
            $this->makeDelegate($this->once())
        );
    }

    public function testNextIfUser()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $request = (new ServerRequest('GET', '/demo/'));
        $this->makeMiddleware($user)->process(
            $request,
            $this->makeDelegate($this->once())
        );
    }
}
