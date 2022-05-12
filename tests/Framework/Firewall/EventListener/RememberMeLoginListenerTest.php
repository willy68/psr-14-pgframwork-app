<?php

namespace Tests\Framework\Firewall\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Auth;
use PgFramework\Event\Events;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Security\Firewall\EventListener\RememberMeLoginListener;

class RememberMeLoginListenerTest extends TestCase
{
    private $cookie;
    private $auth;

    public function setUp(): void
    {
        $this->cookie = $this->getMockBuilder(RememberMeInterface::class)->getMock();
        $this->auth = $this->getMockBuilder(Auth::class)->getMock();
    }

    public function makeListener($user)
    {
        $this->auth->expects($this->once())->method('getUser')->willReturn($user);
        return new RememberMeLoginListener($this->auth, $this->cookie);
    }

    public function makeResponseEvent($request, $response)
    {
        $event = $this->getMockBuilder(ResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn($response);
        /** @var ResponseEvent $event */
        return $event;
    }

    public function makeRequestEvent($request)
    {
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        /** @var RequestEvent $event */
        return $event;
    }

    public function testThrowIfNoUserLoggedInAndNoUserInRequestAttribute()
    {
        $request = new ServerRequest('GET', '/demo/');
        $this->cookie->method('autoLogin')->willReturn($request);
        $this->expectException(Auth\ForbiddenException::class);
        $this->makeListener(null)->onAuthentication($this->makeRequestEvent($request));
    }

    public function testIfNoUserLoggedInAndUserInRequestAttribute()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $request = new ServerRequest('GET', '/demo/');
        $this->cookie->method('autoLogin')->willReturn($request->withAttribute('_user', $user));
        $this->auth->expects($this->once())->method('setUser')->with($user);
        $this->makeListener(null)->onAuthentication($this->makeRequestEvent($request));
    }

    public function testNextIfUser()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $request = (new ServerRequest('GET', '/demo/'));
        $this->makeListener($user)->onAuthentication($this->makeRequestEvent($request));
    }

    public function testResumeCookieInResponse()
    {
        $rememberMe = new RememberMeLoginListener($this->auth, $this->cookie);
        $response = (new Response())->withAddedHeader('Set-Cookie', 'auth_login');
        $request = (new ServerRequest('GET', '/demo/'));
        $this->cookie->expects($this->once())->method('resume')->will($this->returnCallback(
            function ($request, $response) {
                $cookieHeaders = $response->getHeader('Set-Cookie');
                $this->assertContains('auth_login', $cookieHeaders);
                return $response;
            }
        ));
        $rememberMe->onResponse($this->makeResponseEvent($request, $response));
    }

    public function testSubscribeEvent()
    {
        $this->assertEquals(
            [
                Events::REQUEST => ['onAuthentication', ListenerPriority::HIGH],
                Events::RESPONSE => ['onResponse', ListenerPriority::LOW]
            ],
            RememberMeLoginListener::getSubscribedEvents()
        );
    }
}
