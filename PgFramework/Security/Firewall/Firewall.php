<?php

namespace PgFramework\Security\Firewall;

use Invoker\CallableResolver;
use League\Event\ListenerPriority;
use PgFramework\Event\Events;
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

    public function __construct(
        EventDispatcherInterface $mainDispatcher,
        FirewallMapInterface $map,
        CallableResolver $callableResolver,
        ListenerProviderInterface $listenerProvider = null
    ) {
        parent::__construct($callableResolver, $listenerProvider);
        $this->mainDispatcher = $mainDispatcher;
        $this->map = $map;
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();

        [$listeners, $mainListeners] = $this->map->getListeners($request);

        foreach ($listeners as $listener) {
            $this->addSubscriber($listener);
        }

        foreach ($mainListeners as $listener) {
            $this->mainDispatcher->addSubscriber($listener);
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
            Events::REQUEST => ListenerPriority::HIGH
        ];
    }
}
