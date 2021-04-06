<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Invoker\CallableResolver;
use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
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
        if (null === $this->auth->getUser()) {
            throw new ForbiddenException('User not found.');
        }

        $request = $event->getRequest();

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $event->getApp()->getDispatcher();

        /** @var CallableResolver $callableResolver*/
        $callableResolver = $event->getApp()->getContainer()->get(CallableResolver::class);

        [$attributes, $listeners] = $this->map->getPatterns($request);

        foreach ($listeners as $listener => $eventName) {
            $priority = ListenerPriority::NORMAL;
            if (is_array($eventName)) {
                [$eventName, $priority] = $eventName;
            }
            $dispatcher->subscribeTo(
                $eventName,
                $callableResolver->resolve($listener),
                $priority
            );
        }

        if (!$this->voterManager->decide($this->auth, $attributes, $request, true)) {
            $exception = new ForbiddenException('Vous n\'avez pas l\'authorisation pour executer cette action');
            throw $exception;
        }
        $event->setRequest($request);
    }
}
