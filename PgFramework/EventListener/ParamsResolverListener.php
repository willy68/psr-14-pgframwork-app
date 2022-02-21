<?php

namespace PgFramework\EventListener;

use PgFramework\Event\Events;
use Invoker\Reflection\CallableReflection;
use PgFramework\Event\ControllerParamsEvent;
use Invoker\ParameterResolver\ParameterResolver;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class ParamsResolverListener implements EventSubscriberInterface
{
    private $paramsResolver;

    public function __construct(ParameterResolver $paramsResolver)
    {
        $this->paramsResolver = $paramsResolver;
    }

    public function onResolve(ControllerParamsEvent $event)
    {
        $controller = $event->getController();
        $params = $event->getParams();
        $callableReflection = CallableReflection::create($controller);
        $params = $this->paramsResolver->getParameters($callableReflection, $params, []);
        $event->setParams($params);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::PARAMETERS => ['onResolve', 10]
        ];

    }
}
