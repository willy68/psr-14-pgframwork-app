<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Framework\Security\Firewall\Event\AuthenticationEvent;

class LoggedInListener
{

    private $auth;

    /**
     * LoggedInMiddleware constructor.
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @throws ForbiddenException
     */
    public function onAuthenticationEvent(AuthenticationEvent $event)
    {
        $request = $event->getRequest();
        $user = $this->auth->getUser();
        if (!$user) {
            throw new ForbiddenException('Vous n\'êtes pas connecté');
        }
        $event->setRequest($request->withAttribute('user', $user));
    }
}
