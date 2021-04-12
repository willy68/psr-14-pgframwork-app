<?php

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth;
use PgFramework\Event\ResponseEvent;
use PgFramework\Auth\ForbiddenException;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Security\Firewall\Event\AuthenticationEvent;

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
            $event->setRequest($request->withAttribute('cancel.rememberme.cookie', true));
            throw new ForbiddenException("Cookie invalid");
        }
        $this->auth->setUser($user);
    }

    public function onResponseEvent(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $event->setResponse($this->cookie->resume($request, $response));
    }
}
