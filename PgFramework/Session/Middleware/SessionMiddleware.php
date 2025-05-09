<?php

declare(strict_types=1);

namespace PgFramework\Session\Middleware;

use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Session\SessionPersistenceInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public const SESSION_ATTRIBUTE = '_session';

    private SessionPersistenceInterface $persistence;

    private SessionInterface $session;

    public function __construct(SessionPersistenceInterface $persistence, SessionInterface $session)
    {
        $this->persistence = $persistence;
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle(
            $request
                ->withAttribute(self::SESSION_ATTRIBUTE, $this->session)
                ->withAttribute(SessionInterface::class, $this->session)
        );
        return $this->persistence->persistSession($this->session, $response);
    }
}
