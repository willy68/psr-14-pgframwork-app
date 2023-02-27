<?php

declare(strict_types=1);

namespace PgFramework\EventListener;

use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use Invoker\Invoker;
use League\Event\Listener;
use Invoker\InvokerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Invoker\ParameterResolver\{
    ResolverChain,
    DefaultValueResolver,
    NumericArrayResolver,
    AssociativeArrayResolver};
use PgFramework\Invoker\ParameterResolver\ActiveRecordResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use PgFramework\Annotation\AnnotationsLoader;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;

/**
 * Listener that depend on Container
 */
class InvokerListener implements Listener
{
    protected mixed $callback;

    protected ContainerInterface $container;

    protected InvokerInterface $invoker;

    /**
     *
     * @param mixed $callback
     * @param ContainerInterface $container
     */
    public function __construct(mixed $callback, ContainerInterface $container)
    {
        $this->callback = $callback;
        $this->container = $container;
    }

    /**
     * Call the callback with $event parameter
     *
     * @param object $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(object $event): void
    {
        $this->getInvoker($this->container)->call($this->callback, [$event]);
    }

    /**
     * Crée un Invoker
     *
     * @param ContainerInterface $container
     * @return InvokerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getInvoker(ContainerInterface $container): InvokerInterface
    {
        if (!$this->invoker) {
            $parameterResolver = new ResolverChain([
                new ActiveRecordAnnotationsResolver($container->get(AnnotationsLoader::class)),
                new ActiveRecordResolver(),
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($container)
            ]);
            $this->invoker = new Invoker($parameterResolver, $container);
        }
        return $this->invoker;
    }
}
