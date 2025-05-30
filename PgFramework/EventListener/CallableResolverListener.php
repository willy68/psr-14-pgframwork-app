<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use PgFramework\Event\Events;
use PgFramework\Event\ControllerEvent;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use ReflectionException;

class CallableResolverListener implements EventSubscriberInterface
{
    private CallableResolver $callableResolver;

    public function __construct(CallableResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * @throws NotCallableException
     * @throws ReflectionException
     */
    public function onResolve(ControllerEvent $event)
    {
        $controller = $event->getController();
        $controller = $this->callableResolver->resolve($controller);
        $event->setController($controller);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CONTROLLER => ['onResolve', 10]
        ];
    }
}
