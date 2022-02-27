<?php

namespace PgFramework\EventListener;

use Invoker\CallableResolver;
use PgFramework\Event\Events;
use PgFramework\Event\ControllerEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class CallableResolverListener implements EventSubscriberInterface
{
    private $callableResolver;

    public function __construct(CallableResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    public function onResolve(ControllerEvent $event)
    {
        $controller = $event->getController();
        $controller = $this->callableResolver->resolve($controller);
        $event->setController($controller);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CONTROLLER => ['onResolve', 10]
        ];
    }
}
