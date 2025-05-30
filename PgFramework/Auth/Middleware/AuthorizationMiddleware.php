<?php

declare(strict_types=1);

namespace PgFramework\Auth\Middleware;

use PgFramework\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\ForbiddenException;
use Psr\Http\Server\MiddlewareInterface;
use PgFramework\Auth\FailedAccessException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Security\Firewall\AccessMapInterface;
use PgFramework\Security\Authorization\VoterManagerInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    protected Auth $auth;
    protected VoterManagerInterface $voterManager;
    protected AccessMapInterface $map;

    public function __construct(
        Auth $auth,
        VoterManagerInterface $voterManager,
        AccessMapInterface $map
    ) {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
        $this->map = $map;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws FailedAccessException
     * @throws ForbiddenException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$attributes] = $this->map->getPatterns($request);

        if (!$attributes) {
            return $handler->handle($request);
        }

        if (null === $this->auth->getUser()) {
            throw new ForbiddenException('User not found.');
        }

        if (!$this->voterManager->decide($this->auth, $attributes, $request)) {
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour exécuter cette action');
        }
        return $handler->handle($request);
    }
}
