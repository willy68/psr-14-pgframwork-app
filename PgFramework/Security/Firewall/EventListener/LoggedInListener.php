<?php

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Auth\ForbiddenException;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class LoggedInListener implements EventSubscriberInterface
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
    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();
        $user = $this->auth->getUser();
        if (!$user) {
            throw new ForbiddenException('Vous n\'êtes pas connecté');
        }
        $event->setRequest($request->withAttribute('_user', $user));
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
