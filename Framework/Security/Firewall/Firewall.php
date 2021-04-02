<?php

namespace Framework\Security\Firewall;

use Invoker\CallableResolver;
use Framework\Event\RequestEvent;
use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use Framework\Router\RequestMatcher;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Security\Firewall\Event\AuthenticationEvent;
use Framework\Security\Firewall\Event\AuthorizationEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class Firewall extends EventDispatcher
{

    /**
     *
     * @var EventDispatcher
     */
    protected $mainDispatcher;

    public function __construct(
        EventDispatcherInterface $mainDispatcher,
        ListenerProviderInterface $listenerProvider = null
    ) {
        parent::__construct($listenerProvider);
        $this->mainDispatcher = $mainDispatcher;
    }

    public function onRequestEvent(RequestEvent $event)
    {
        $container = $event->getApp()->getContainer();

        if (!$container->has('firewall.event.rules')) {
            return;
        }
        $rules = $container->get('firewall.event.rules');

        $request = $event->getRequest();

        [$listeners, $mainListeners] = $this->getListeners($request, $rules);

        /** @var CallableResolver $callableResolver*/
        $callableResolver = $container->get(CallableResolver::class);

        foreach ($listeners as $listener => $eventName) {
            $priority = ListenerPriority::NORMAL;
            if (is_array($eventName)) {
                [$eventName, $priority] = $eventName;
            }
            $this->subscribeTo(
                $eventName,
                $callableResolver->resolve($listener),
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
                $callableResolver->resolve($listener),
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

    protected function getListeners(ServerRequestInterface $request, array $rules)
    {

        $listeners = [[], []];

            foreach ($rules as $rule) {
                $requestMatcher = new RequestMatcher;

                if (\array_key_exists('path', $rule)) {
                    $requestMatcher->setPath($rule['path']);
                }

                if (\array_key_exists('method', $rule)) {
                    $requestMatcher->setMethod($rule['method']);
                }

                if (\array_key_exists('host', $rule)) {
                    $requestMatcher->setHost($rule['host']);
                }

                if (\array_key_exists('scheme', $rule)) {
                    $requestMatcher->setSchemes($rule['scheme']);
                }

                if (\array_key_exists('port', $rule)) {
                    $requestMatcher->setPort($rule['port']);
                }

                if ($requestMatcher->match($request)) {
                    if (\array_key_exists('listeners', $rule)) {
                        $listeners[0] = $rule['listeners'];
                    }
                    if (\array_key_exists('main.listeners', $rule)) {
                        $listeners[1] =  $rule['main.listeners'];
                    }
                }
                unset($requestMatcher);
            }

        return $listeners;
    }
}
