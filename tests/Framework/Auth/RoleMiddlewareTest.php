<?php

namespace Tests\Framework\Auth;

use Framework\Auth;
use Prophecy\Argument;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Framework\Auth\RoleMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Server\RequestHandlerInterface;

class RoleMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    private $middleware;

    private $auth;

    public function setUp(): void
    {
        $this->auth = $this->prophesize(Auth::class);
        $this->middleware = new RoleMiddleware(
            $this->auth->reveal(),
            'admin'
        );
    }

    public function testWithUnauthenticatedUser()
    {
        $this->auth->getUser()->willReturn(null);
        $this->expectException(Auth\ForbiddenException::class);
        $this->middleware->process(new ServerRequest('GET', '/demo'), $this->makeDelegate()->reveal());
    }

    public function testWithBadRole()
    {
        $user = $this->prophesize(Auth\User::class);
        $user->getRoles()->willReturn(['user']);
        $this->auth->getUser()->willReturn($user->reveal());
        $this->expectException(Auth\ForbiddenException::class);
        $this->middleware->process(new ServerRequest('GET', '/demo'), $this->makeDelegate()->reveal());
    }

    public function testWithGoodRole()
    {
        $user = $this->prophesize(Auth\User::class);
        $user->getRoles()->willReturn(['admin']);
        $this->auth->getUser()->willReturn($user->reveal());
        $delegate = $this->makeDelegate();
        $delegate
            ->handle(Argument::any())
            ->shouldBeCalled()
            ->willReturn(new Response());
        $this->middleware->process(new ServerRequest('GET', '/demo'), $delegate->reveal());
    }

    private function makeDelegate(): ObjectProphecy
    {
        $delegate = $this->prophesize(RequestHandlerInterface::class);
        $delegate->handle(Argument::any())->willReturn(new Response());
        return $delegate;
    }
}
