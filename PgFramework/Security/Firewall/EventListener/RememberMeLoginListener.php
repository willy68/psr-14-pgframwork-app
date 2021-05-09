<?php

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use PgFramework\Auth\ForbiddenException;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class RememberMeLoginListener implements EventSubscriberInterface
{

    private $auth;

    private $rememberMe;

    public function __construct(Auth $auth, RememberMeInterface $rememberMe)
    {
        $this->auth = $auth;
        $this->rememberMe = $rememberMe;
    }

    public function onAuthentication(RequestEvent $event)
    {
        $request = $event->getRequest();

        $user = $this->auth->getUser();
        if ($user) {
            return;
        }
        $request = $this->rememberMe->autoLogin($request);
        $event->setRequest($request);
        if (!($user = $request->getAttribute('_user'))) {
            throw new ForbiddenException("Cookie invalid");
        }
        $this->auth->setUser($user);
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $event->setResponse($this->rememberMe->resume($request, $response));
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ['onAuthentication', ListenerPriority::HIGH],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW]
        ];
    }
}
