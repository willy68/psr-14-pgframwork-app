<?php

namespace PgFramework\EventDispatcher;

use Invoker\CallableResolver;
use League\Event\ListenerPriority;
use Psr\EventDispatcher\ListenerProviderInterface;
use League\Event\EventDispatcher as LeagueEventDispatcher;

class EventDispatcher extends LeagueEventDispatcher
{
    protected $callableResolver;

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
     * @param EventSubscriberInterface|string $subscriber
     * @return void
     */
    function addSubscriber($subscriber)
    {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            // eventName in $params default __invoke and priority
            if (is_int($eventName)) {
                $this->subscribeTo($params, $this->callableResolver->resolve($subscriber));
            }
            // default priority
            else if (\is_string($params)) {
                $this->subscribeTo($eventName, $this->callableResolver->resolve([$subscriber, $params]));
            }
            // default __invoke and priority in $params 
            else if (\is_int($params)) {
                $this->subscribeTo($eventName, $this->callableResolver->resolve($subscriber), $params);
            }
            // Array of method and priority (or default to 0)
            elseif (\is_string($params[0])) {
                $this->subscribeTo($eventName, $this->callableResolver->resolve([$subscriber, $params[0]]), $params[1] ?? 0);
            }
        }
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
     */
    public function addListeners(array $listeners)
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
