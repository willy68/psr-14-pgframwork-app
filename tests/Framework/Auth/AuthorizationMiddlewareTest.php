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

    public function setUp(): void
    {
        $this->voterManager = $this->getMockBuilder(VoterManager::class)->getMock();
    }

    public function makeMiddleware($user)
    {
        $auth = $this->getMockBuilder(Auth::class)->getMock();
        $auth->method('getUser')->willReturn($user);
        $accesMap = $this->getMockBuilder(AccessMapInterface::class)->getMock();
        $accesMap->method('getPatterns')->willReturn([['ROLE_ADMIN']]);
        /** @var Auth $auth */
        /** @var AccessMapInterface $accesMap */
        return new AuthorizationMiddleware($auth, $this->voterManager, $accesMap);
    }

    public function makeDelegate($calls)
    {
        $delegate = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $delegate->expects($calls)->method('handle')->willReturn($response);
        /** @var RequestHandlerInterface $delegate */
        return $delegate;
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
        $this->voterManager->method('decide')->willReturn(false);
        $request = (new ServerRequest('GET', '/demo/'));
        $this->expectException(Auth\FailedAccessException::class);
        $this->makeMiddleware($user)->process(
            $request,
            $this->makeDelegate($this->never())
        );
    }
}
