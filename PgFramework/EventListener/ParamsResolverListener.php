<?php

namespace PgFramework\EventListener;

use PgFramework\Event\Events;
use Psr\Container\ContainerInterface;
use Invoker\Reflection\CallableReflection;
use PgFramework\Event\ControllerParamsEvent;
use Psr\Http\Message\ServerRequestInterface;
use Invoker\ParameterResolver\ParameterResolver;
use PgFramework\EventDispatcher\EventSubscriberInterface;

class ParamsResolverListener implements EventSubscriberInterface
{
    private $paramsResolver;

    private $container;

    public function __construct(ContainerInterface $container, ParameterResolver $paramsResolver)
    {
        $this->container = $container;
        $this->paramsResolver = $paramsResolver;
    }

    public function onResolve(ControllerParamsEvent $event)
    {
        $controller = $event->getController();
        $params = $event->getParams();

        // Ajoute la requète à jour
        if ($this->container instanceof \DI\Container) {
            $this->container->set(ServerRequestInterface::class, $event->getRequest());
        } else {
            // Limitation: $request must be named "$request"
            $params = array_merge(["request" => $event->getRequest()], $params);
        };

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
