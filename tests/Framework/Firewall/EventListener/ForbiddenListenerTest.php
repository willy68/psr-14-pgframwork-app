<?php

namespace Tests\Framework\Firewall\EventListener;

use GuzzleHttp\Psr7\Response;
use PgFramework\Event\Events;
use PHPUnit\Framework\TestCase;
use League\Event\ListenerPriority;
use Psr\Http\Message\UriInterface;
use Mezzio\Session\SessionInterface;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Event\ExceptionEvent;
use PgFramework\Session\ArraySession;
use PgFramework\Auth\ForbiddenException;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Security\Firewall\EventListener\ForbiddenListener;

class ForbiddenListenerTest extends TestCase
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

    public function makeExceptionEvent($request, $exception)
    {
        $event = $this->getMockBuilder(ExceptionEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $event->method('getException')->willReturn($exception);
        return $event;
    }

    public function makeListener()
    {
        $session = $this->session;
        /** @var SessionInterface $session */
        return new ForbiddenListener('/login', $session);
    }

    public function testCatchForbiddenException()
    {
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $event = $this->makeExceptionEvent($request, new ForbiddenException());
        $event->expects($this->once())->method('setResponse')->with($this->callback(function () {
            new Response(301, ['Location' => '/login']);
            return true;
        }));
        ($this->makeListener())($event);
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testCatchForbiddenExceptionJson()
    {
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('application/json');
        $event = $this->makeExceptionEvent($request, new ForbiddenException());
        $event->expects($this->once())->method('setResponse')->with($this->callback(function () {
            new Response(403);
            return true;
        }));
        ($this->makeListener())($event);
    }

    public function testCatchFailedAccessException()
    {
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getServerParams')->willReturn(['HTTP_REFERER' => '/test']);
        $event = $this->makeExceptionEvent($request, new FailedAccessException());
        $event->expects($this->once())->method('setResponse')->with($this->callback(function () {
            new Response(301, ['Location' => '/test']);
            return true;
        }));
        ($this->makeListener())($event);
    }

    public function testCatchFailedAccessExceptionJson()
    {
        $request = $this->makeRequest('/test');
        $request->method('getHeaderLine')->willReturn('application/json');
        $request->method('getServerParams')->willReturn(['HTTP_REFERER' => '/test']);
        $event = $this->makeExceptionEvent($request, new FailedAccessException());
        $event->expects($this->once())->method('setResponse')->with($this->callback(function () {
            new Response(403);
            return true;
        }));
        ($this->makeListener())($event);
    }

    public function testSubscribeEvent()
    {
        $this->assertEquals(
            [Events::EXCEPTION => ListenerPriority::HIGH],
            ForbiddenListener::getSubscribedEvents()
        );
    }
}
