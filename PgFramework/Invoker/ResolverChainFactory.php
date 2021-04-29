<?php

namespace PgFramework\Invoker;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineEntityResolver;

class ResolverChainFactory
{
    public function __invoke(ContainerInterface $container): ParameterResolver
    {
        return new ControllerParamsResolver([
        new ActiveRecordAnnotationsResolver(),
        new ActiveRecordResolver(),
        new DoctrineEntityResolver($container->get(EntityManager::class)),
        new NumericArrayResolver(),
        new AssociativeArrayResolver(),
        new DefaultValueResolver(),
        new TypeHintContainerResolver($container)
        ]);
    }
}
