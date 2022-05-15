<?php

namespace Tests\Framework\Firewall\EventListener;

use PgFramework\Auth;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use PgFramework\Event\RequestEvent;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Security\Authorization\VoterManager;
use PgFramework\Security\Firewall\AccessMapInterface;
use PgFramework\Security\Firewall\EventListener\AuthorizationListener;

class AuthorizationListenerTest extends TestCase
{
    private $voterManager;

    private $auth;

    private $accesMap;

    public function setUp(): void
    {
        $this->voterManager = $this->getMockBuilder(VoterManager::class)->getMock();
        $this->auth = $this->getMockBuilder(Auth::class)->getMock();
        $this->accesMap = $this->getMockBuilder(AccessMapInterface::class)->getMock();
    }

    public function makeListener($user, $attributes = [['ROLE_ADMIN']], $vote = true)
    {
        $this->auth->method('getUser')->willReturn($user);
        $this->accesMap->method('getPatterns')->willReturn($attributes);
        $this->voterManager->method('decide')->willReturn($vote);
        return new AuthorizationListener($this->auth, $this->voterManager, $this->accesMap);
    }

    public function makeRequestEvent($request)
    {
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequest')->willReturn($request);
        return $event;
    }

    public function testNextIfNoAttributes()
    {
        $request = (new ServerRequest('GET', '/demo'));
        $event = $this->makeRequestEvent($request);
        $this->auth->expects($this->never())->method('getUser');
        $event->expects($this->never())->method('setRequest');
        $this->voterManager->expects($this->never())->method('decide');
        $this->makeListener(null, [[]])($event);
    }

    public function testNextIfUserAdmin()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $this->auth->expects($this->once())->method('getUser')->willReturn($user);
        $this->voterManager->expects($this->once())->method('decide')->willReturn(true);
        $request = (new ServerRequest('GET', '/demo'));
        $event = $this->makeRequestEvent($request);
        $event->expects($this->once())->method('setRequest')->with($request);
        $this->makeListener($user)($event);
    }

    public function testThrowIfNoAdmin()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $this->auth->expects($this->once())->method('getUser')->willReturn($user);
        $this->voterManager->expects($this->once())->method('decide')->willReturn(false);
        $request = (new ServerRequest('GET', '/demo'));
        $event = $this->makeRequestEvent($request);
        $event->expects($this->never())->method('setRequest')->with($request);
        $this->expectException(FailedAccessException::class);
        $this->makeListener($user, [['ROLE_ADMIN']], false)($event);
    }
}
