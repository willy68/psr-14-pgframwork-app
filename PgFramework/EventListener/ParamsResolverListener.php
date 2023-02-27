<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use DI\Container;
use Invoker\Exception\NotCallableException;
use PgFramework\Event\Events;
use Psr\Container\ContainerInterface;
use Invoker\Reflection\CallableReflection;
use PgFramework\Event\ControllerParamsEvent;
use Psr\Http\Message\ServerRequestInterface;
use Invoker\ParameterResolver\ParameterResolver;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use ReflectionException;

class ParamsResolverListener implements EventSubscriberInterface
{
    private ParameterResolver $paramsResolver;

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container, ParameterResolver $paramsResolver)
    {
        $this->container = $container;
        $this->paramsResolver = $paramsResolver;
    }

    /**
     * @throws ReflectionException
     * @throws NotCallableException
     */
    public function onResolve(ControllerParamsEvent $event)
    {
        $controller = $event->getController();
        $params = $event->getParams();

        // Ajoute la request à jour
        if ($this->container instanceof Container) {
            $this->container->set(ServerRequestInterface::class, $event->getRequest());
        } else {
            // Limitation: $request must be named "$request" on your controller
            $params = array_merge(["request" => $event->getRequest()], $params);
        }

        $callableReflection = CallableReflection::create($controller);
        $params = $this->paramsResolver->getParameters($callableReflection, $params, []);
        $event->setParams($params);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::PARAMETERS => ['onResolve', 10]
        ];
    }
}
