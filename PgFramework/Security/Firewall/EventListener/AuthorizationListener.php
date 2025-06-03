<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Auth\Auth;
use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Auth\ForbiddenException;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Security\Firewall\AccessMapInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\Security\Authorization\VoterManagerInterface;

class AuthorizationListener implements EventSubscriberInterface
{
    protected Auth $auth;
    protected VoterManagerInterface $voterManager;
    protected AccessMapInterface $map;

    public function __construct(
        Auth $auth,
        VoterManagerInterface $voterManager,
        AccessMapInterface $map
    ) {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
        $this->map = $map;
    }

    /**
     * @throws ForbiddenException
     * @throws FailedAccessException
     */
    public function __invoke(RequestEvent $event): void
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
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour exécuter cette action');
        }
        $event->setRequest($request->withAttribute('_user', $this->auth->getUser()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => ListenerPriority::LOW
        ];
    }
}
