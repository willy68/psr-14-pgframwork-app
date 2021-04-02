<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth;
use Framework\Auth\RememberMe\RememberMeInterface;
use Framework\Event\ResponseEvent;

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

    public function onResponseEvent(ResponseEvent $event)
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
