<?php

namespace PgFramework\EventDispatcher;

use Invoker\CallableResolver;
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
}
