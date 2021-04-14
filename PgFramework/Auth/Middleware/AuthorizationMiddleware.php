<?php

namespace PgFramework\Auth\Middleware;

use PgFramework\Auth;
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

    protected $auth;
    protected $voterManager;
    protected $map;

    public function __construct(
        Auth $auth,
        VoterManagerInterface $voterManager,
        AccessMapInterface $map
    ) {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
        $this->map = $map;
    }

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
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour executer cette action');
        }
        return $handler->handle($request);
    }
}
