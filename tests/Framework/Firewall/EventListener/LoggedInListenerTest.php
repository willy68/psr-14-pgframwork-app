<?php

namespace Tests\Framework\Firewall\EventListener;

use PgFramework\Auth;
use PgFramework\Event\Events;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Security\Firewall\EventListener\LoggedInListener;

class LoggedInListenerTest extends TestCase
{
    public function makeListener($user)
    {
        $auth = $this->getMockBuilder(Auth::class)->getMock();
        $auth->expects($this->once())->method('getUser')->willReturn($user);
        /** @var Auth $auth */
        return new LoggedInListener($auth);
    }

    public function makeRequestEvent($request)
    {
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequest')->willReturn($request);
        return $event;
    }

    public function testThrowIfNoUser()
    {
        $request = (new ServerRequest('GET', '/demo'));
        $event = $this->makeRequestEvent($request);
        $this->expectException(Auth\ForbiddenException::class);
        $this->makeListener(null)($event);
    }

    public function testNextIfUser()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $request = (new ServerRequest('GET', '/demo'))->withAttribute('_user', $user);
        $event = $this->makeRequestEvent($request);
        $event->expects($this->once())->method('setRequest')->with($request);
        $this->makeListener($user)($event);
    }

    public function testSubscribeEvent()
    {
        $this->assertEquals(
            [Events::REQUEST => ListenerPriority::HIGH],
            LoggedInListener::getSubscribedEvents()
        );
    }
}
