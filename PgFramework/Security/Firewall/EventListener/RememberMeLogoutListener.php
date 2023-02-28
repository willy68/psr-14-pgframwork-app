<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth\Auth;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\ResponseEvent;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class RememberMeLogoutListener implements EventSubscriberInterface
{
    private Auth $auth;

    private RememberMeInterface $cookie;

    public function __construct(Auth $auth, RememberMeInterface $cookie)
    {
        $this->auth = $auth;
        $this->cookie = $cookie;
    }

    public function __invoke(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $user = $this->auth->getUser();
        if (!$user) {
            $response = $this->cookie->onLogout($request, $response);
        }
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::RESPONSE => ListenerPriority::HIGH
        ];
    }
}
