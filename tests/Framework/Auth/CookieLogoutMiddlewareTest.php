<?php

namespace Tests\Framework\Auth;

use PgFramework\Auth;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use PgFramework\Auth\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Auth\Middleware\CookieLogoutMiddleware;

class CookieLogoutMiddlewareTest extends TestCase
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
        return new CookieLogoutMiddleware($this->auth, $this->cookie);
    }

    public function makeDelegate($calls)
    {
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate->expects($calls)->method('handle')->willReturn($response);
        /** @var RequestHandlerInterface $delegate */
        return $delegate;
    }

    public function testLogout()
    {
        $request = new ServerRequest('GET', '/demo/');
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $this->cookie->expects($this->once())->method('onLogout')->willReturn($response);

        $this->makeMiddleware(null)->process(
            $request,
            $this->makeDelegate($this->once())
        );
    }

    public function testIfUser()
    {
        $request = new ServerRequest('GET', '/demo/');
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $this->cookie->expects($this->never())->method('onLogout');

        $this->makeMiddleware($user)->process(
            $request,
            $this->makeDelegate($this->once())
        );
    }
}
