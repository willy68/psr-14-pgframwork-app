<?php

namespace Tests\Framework\Auth;

use PgFramework\Auth;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Security\Authorization\VoterManager;
use PgFramework\Security\Firewall\AccessMapInterface;
use PgFramework\Auth\Middleware\AuthorizationMiddleware;

class AuthorizationMiddlewareTest extends TestCase
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

    public function makeMiddleware($user, $attributes = [['ROLE_ADMIN']], $vote = true)
    {
        $this->auth->method('getUser')->willReturn($user);
        $this->accesMap->method('getPatterns')->willReturn($attributes);
        $this->voterManager->method('decide')->willReturn($vote);
        return new AuthorizationMiddleware($this->auth, $this->voterManager, $this->accesMap);
    }

    public function makeDelegate($calls)
    {
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate->expects($calls)->method('handle')->willReturn($response);
        /** @var RequestHandlerInterface $delegate */
        return $delegate;
    }

    public function testNextIfNoAttributes()
    {
        $request = (new ServerRequest('GET', '/demo/'));
        $this->makeMiddleware(null, [[]])->process(
            $request,
            $this->makeDelegate($this->once())
        );
    }

    public function testThrowIfNoUser()
    {
        $request = (new ServerRequest('GET', '/demo/'));
        $this->expectException(Auth\ForbiddenException::class);
        $this->makeMiddleware(null)->process(
            $request,
            $this->makeDelegate($this->never())
        );
    }

    public function testNextIfUserAdmin()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $this->voterManager->method('decide')->willReturn(true);
        $request = (new ServerRequest('GET', '/demo/'));
        $this->makeMiddleware($user)->process(
            $request,
            $this->makeDelegate($this->once())
        );
    }

    public function testThrowIfNoAdmin()
    {
        $user = $this->getMockBuilder(Auth\UserInterface::class)->getMock();
        $request = (new ServerRequest('GET', '/demo/'));
        $this->expectException(Auth\FailedAccessException::class);
        $this->makeMiddleware($user, [['ROLE_ADMIN']], false)->process(
            $request,
            $this->makeDelegate($this->never())
        );
    }
}
