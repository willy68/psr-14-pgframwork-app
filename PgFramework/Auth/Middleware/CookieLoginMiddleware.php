<?php

declare(strict_types=1);

namespace PgFramework\Auth\Middleware;

use PgFramework\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;

class CookieLoginMiddleware implements MiddlewareInterface
{
    /**
     *
     * @var Auth
     */
    private $auth;

    /**
     *
     *
     * @var RememberMeInterface
     */
    private $cookie;

    public function __construct(Auth $auth, RememberMeInterface $cookie)
    {
        $this->auth = $auth;
        $this->cookie = $cookie;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->getUser();
        if ($user) {
            return $handler->handle($request);
        }
        $request = $this->cookie->autoLogin($request);
        if (($user = $request->getAttribute('_user'))) {
            $this->auth->setUser($user);
        }
        $response = $handler->handle($request);
        return $this->cookie->resume($request, $response);
    }
}
