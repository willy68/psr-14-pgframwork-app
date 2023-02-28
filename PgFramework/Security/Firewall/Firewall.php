<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use PgFramework\Event\Events;
use PgFramework\Event\RequestEvent;
use PgFramework\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use ReflectionException;

class Firewall extends EventDispatcher implements EventSubscriberInterface
{
    protected EventDispatcher|EventDispatcherInterface $mainDispatcher;

    protected FirewallMapInterface $map;

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

    /**
     * @throws ReflectionException
     * @throws NotCallableException
     */
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

        $this->dispatch($event);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST => 300
        ];
    }
}
