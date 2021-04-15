<?php

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Event\ResponseEvent;

class RememberMeLogoutListener
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

    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $user = $this->auth->getUser();
        if (!$user) {
            $response = $this->cookie->onLogout($request, $response);
        }
        $event->setResponse($response);
    }
}
