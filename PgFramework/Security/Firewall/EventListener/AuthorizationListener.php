<?php

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth;
use PgFramework\Auth\ForbiddenException;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Security\Firewall\AccessMapInterface;
use PgFramework\Security\Firewall\Event\AuthorizationEvent;
use PgFramework\Security\Authorization\VoterManagerInterface;

class AuthorizationListener
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

    public function onAuthorization(AuthorizationEvent $event)
    {
        $request = $event->getRequest();

        [$attributes] = $this->map->getPatterns($request);

        if (!$attributes) {
            return;
        }

        if (null === $this->auth->getUser()) {
            throw new ForbiddenException('User not found.');
        }

        if (!$this->voterManager->decide($this->auth, $attributes, $request)) {
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour executer cette action');
        }
        $event->setRequest($request);
    }
}
