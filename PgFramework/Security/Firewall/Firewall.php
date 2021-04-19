<?php

namespace PgFramework\Security\Firewall;

use Invoker\CallableResolver;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class Firewall extends EventDispatcher implements EventSubscriberInterface
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
        parent::__construct($callableResolver, $listenerProvider);
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

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => ListenerPriority::HIGH
        ];
    }
}
