<?php

declare(strict_types=1);

namespace PgFramework\Auth\Middleware;

use PgFramework\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;

class CookieLogoutMiddleware implements MiddlewareInterface
{
    private Auth $auth;

    private RememberMeInterface $cookie;

    public function __construct(Auth $auth, RememberMeInterface $cookie)
    {
        $this->auth = $auth;
        $this->cookie = $cookie;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $user = $this->auth->getUser();
        if (!$user) {
            return $this->cookie->onLogout($request, $response);
        }
        return $response;
    }
}
