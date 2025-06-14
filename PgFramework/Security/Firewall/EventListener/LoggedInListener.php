<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth\Auth;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Auth\ForbiddenException;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class LoggedInListener implements EventSubscriberInterface
{
    private Auth $auth;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @throws ForbiddenException
     */
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $user = $this->auth->getUser();
        if (!$user) {
            throw new ForbiddenException('Vous n\'êtes pas connecté');
        }
        $event->setRequest($request->withAttribute('_user', $user));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
