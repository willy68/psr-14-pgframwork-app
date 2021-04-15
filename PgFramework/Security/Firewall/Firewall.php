<?php

namespace PgFramework\Security\Firewall;

use Invoker\CallableResolver;
use PgFramework\Event\RequestEvent;
use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

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

    public function __invoke(RequestEvent $event)
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

        $event = $this->dispatch($event);

        if ($event->hasResponse()) {
            $event->setResponse($event->getResponse());
            return;
        }
    }
}
