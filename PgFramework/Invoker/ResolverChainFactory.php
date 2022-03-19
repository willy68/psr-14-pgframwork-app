<?php

namespace PgFramework\Invoker;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineEntityResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use PgFramework\Annotation\AnnotationsLoader;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineParamConverterAnnotations;

class ResolverChainFactory
{
    public function __invoke(ContainerInterface $container): ParameterResolver
    {
        return new ControllerParamsResolver([
            new ActiveRecordAnnotationsResolver($container->get(AnnotationsLoader::class)),
            new ActiveRecordResolver(),
            new DoctrineParamConverterAnnotations(
                $container->get(EntityManager::class),
                $container->get(AnnotationsLoader::class)
            ),
            new DoctrineEntityResolver($container->get(EntityManager::class)),
            new NumericArrayResolver(),
            new AssociativeArrayResolver(),
            new DefaultValueResolver(),
            new TypeHintContainerResolver($container)
        ]);
    }
}
