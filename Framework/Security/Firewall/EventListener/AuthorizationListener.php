<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth;
use Framework\Event\Events;
use Invoker\CallableResolver;
use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use Framework\Auth\ForbiddenException;
use Framework\Security\Firewall\AccessMapInterface;
use Framework\Security\Firewall\Event\AuthorizationEvent;
use Framework\Security\Authorization\VoterManagerInterface;

class AuthorizationListener
{
    protected $auth;
    protected $voterManager;
    protected $map;

    public function __construct(
        Auth $auth,
        VoterManagerInterface $voterManager,
        AccessMapInterface $map
    ) {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
        $this->map = $map;
    }

    public function onAuthorization(AuthorizationEvent $event)
    {
        $request = $event->getRequest();

        [$attributes, $listeners] = $this->map->getPatterns($request);

        if (!$attributes) {
            return;
        }

        if (null === $this->auth->getUser()) {
            throw new ForbiddenException('User not found.');
        }

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $event->getApp()->getDispatcher();

        /** @var CallableResolver $callableResolver*/
        $callableResolver = $event->getApp()->getContainer()->get(CallableResolver::class);

        foreach ($listeners as $listener => $eventName) {
            $priority = ListenerPriority::NORMAL;
            $eventName = Events::REQUEST;
            if (is_array($eventName)) {
                [$eventName, $priority] = $eventName;
            }
            $dispatcher->subscribeTo(
                $eventName,
                $callableResolver->resolve($listener),
                $priority
            );
        }

        if (!$this->voterManager->decide($this->auth, $attributes, $request)) {
            throw new ForbiddenException('Vous n\'avez pas l\'authorisation pour executer cette action');
        }
        $event->setRequest($request);
    }
}
