<?php

namespace Framework\Security\Firewall;

use Invoker\CallableResolver;
use Framework\Event\RequestEvent;
use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Framework\Security\Firewall\Event\AuthorizationEvent;
use Framework\Security\Firewall\Event\AuthenticationEvent;

class Firewall extends EventDispatcher
{

    /**
     *
     * @var EventDispatcher
     */
    protected $mainDispatcher;

    protected $map;

    protected $callableResolver;

    public function __construct(
        EventDispatcherInterface $mainDispatcher,
        FirewallMapInterface $map,
        CallableResolver $callableResolver,
        ListenerProviderInterface $listenerProvider = null
    ) {
        parent::__construct($listenerProvider);
        $this->mainDispatcher = $mainDispatcher;
        $this->map = $map;
        $this->callableResolver = $callableResolver;
    }

    public function onRequestEvent(RequestEvent $event)
    {
        $request = $event->getRequest();

        [$listeners, $mainListeners] = $this->map->getListeners($request);

        foreach ($listeners as $listener => $eventName) {
            $priority = ListenerPriority::NORMAL;
            if (is_array($eventName)) {
                [$eventName, $priority] = $eventName;
            }
            $this->subscribeTo(
                $eventName,
                $this->callableResolver->resolve($listener),
                $priority
            );
        }

        foreach ($mainListeners as $listener => $eventName) {
            $priority = ListenerPriority::NORMAL;
            if (is_array($eventName)) {
                [$eventName, $priority] = $eventName;
            }
            $this->mainDispatcher->subscribeTo(
                $eventName,
                $this->callableResolver->resolve($listener),
                $priority
            );
        }

        $firewallEvent = new AuthenticationEvent($event->getApp(), $request);
        $this->dispatch($firewallEvent);

        if ($firewallEvent->hasResponse()) {
            $event->setResponse($firewallEvent->getResponse());
            $event->setRequest($firewallEvent->getRequest());
            return;
        }

        $firewallEvent = new AuthorizationEvent($event->getApp(), $firewallEvent->getRequest());
        $this->dispatch($firewallEvent);

        if ($firewallEvent->hasResponse()) {
            $event->setResponse($firewallEvent->getResponse());
            $event->setRequest($firewallEvent->getRequest());
        }
    }
}
