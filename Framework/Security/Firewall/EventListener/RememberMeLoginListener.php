<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Framework\Auth\RememberMe\RememberMeInterface;
use Framework\Security\Firewall\Event\AuthenticationEvent;

class RememberMeLoginListener
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

    public function onAuthenticationEvent(AuthenticationEvent $event)
    {
        $request = $event->getRequest();

        $user = $this->auth->getUser();
        if ($user) {
            return;
        }
        $user = $this->cookie->autoLogin($request);
        if (!$user) {
            throw new ForbiddenException("Cookie invalid");
        }
        $this->auth->setUser($user);
    }
}
