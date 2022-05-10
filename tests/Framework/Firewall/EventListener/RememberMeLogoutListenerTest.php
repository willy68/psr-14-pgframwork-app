<?php

namespace Tests\Framework\Firewall\EventListener;

use PgFramework\Auth;
use PHPUnit\Framework\TestCase;
use PgFramework\Auth\UserInterface;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Event\ResponseEvent;
use PgFramework\Security\Firewall\EventListener\RememberMeLogoutListener;
use Psr\Http\Message\ServerRequestInterface;

class RememberMeLogoutListenerTest extends TestCase
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
        $this->auth->method('getUser')->willReturn($user);
        /** @var Auth $auth */
        return new RememberMeLogoutListener($this->auth, $this->cookie);
    }

    public function makeEvent($request, $response)
    {
        $event = $this->getMockBuilder(ResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('getResponse')->willReturn($response);
        return $event;
    }

    public function testLogout()
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $this->cookie->expects($this->once())->method('onLogout')->willReturn($response);

        ($this->makeListener(null))($this->makeEvent($request, $response));
    }

    public function testIfUser()
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $this->cookie->expects($this->never())->method('onLogout');

        ($this->makeListener($user))($this->makeEvent($request, $response));
    }
}
