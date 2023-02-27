<?php

declare(strict_types=1);

namespace PgFramework\EventDispatcher;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use League\Event\ListenerPriority;
use Psr\EventDispatcher\ListenerProviderInterface;
use League\Event\EventDispatcher as LeagueEventDispatcher;
use ReflectionException;
use function is_int;
use function is_string;

class EventDispatcher extends LeagueEventDispatcher
{
    protected CallableResolver $callableResolver;

    public function __construct(
        CallableResolver $callableResolver,
        ListenerProviderInterface $listenerProvider = null
    ) {
        parent::__construct($listenerProvider);
        $this->callableResolver = $callableResolver;
    }

    /**
     * Subscribe to this dispatcher
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * The priority (default __invoke class method)
     *  * The eventName (default __invoke class method) (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => $priority]
     *  * ['eventName']
     *  * ['eventName' => ['methodName', $priority]]
     *
     * @param string|EventSubscriberInterface $subscriber
     * @return self
     * @throws NotCallableException
     * @throws ReflectionException
     */
    public function addSubscriber(string|EventSubscriberInterface $subscriber): self
    {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            // eventName in $params default __invoke and priority
            if (is_int($eventName)) {
                $this->subscribeTo($params, $this->callableResolver->resolve($subscriber));
            } elseif (is_string($params)) {
                // default priority
                $this->subscribeTo($eventName, $this->callableResolver->resolve([$subscriber, $params]));
            } elseif (is_int($params)) {
                // default __invoke and priority in $params
                $this->subscribeTo($eventName, $this->callableResolver->resolve($subscriber), $params);
            } elseif (is_string($params[0])) {
                // Array of method and priority (or default to 0)
                $this->subscribeTo(
                    $eventName,
                    $this->callableResolver->resolve([$subscriber, $params[0]]),
                    $params[1] ?? 0
                );
            }
        }
        return $this;
    }

    /**
     * Add listeners array to this dispatcher
     *
     * The array keys are callable names and the value can be:
     *
     *  * With __invoke method
     *  * [$listeners::class => [$eventName, $priority]]
     *  * With specific method (CallableResolver resolve this format)
     *  * [$listener::class . "::method" => [$eventName, $priority]]
     *  * With default priority to 0
     *  * [$listeners::class => $eventName]
     *
     * @param array $listeners
     * @return void
     * @throws NotCallableException
     * @throws ReflectionException
     */
    public function addListeners(array $listeners): void
    {
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
    }
}
