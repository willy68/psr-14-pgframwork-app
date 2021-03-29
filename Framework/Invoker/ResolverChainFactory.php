<?php

namespace Framework\Invoker;

use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Framework\Invoker\ParameterResolver\ActiveRecordResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Framework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;

class ResolverChainFactory
{
    public function __invoke(ContainerInterface $container): ParameterResolver
    {
        return new ControllerParamsResolver([
        new ActiveRecordAnnotationsResolver(),
        new ActiveRecordResolver(),
        new NumericArrayResolver(),
        new AssociativeArrayResolver(),
        new DefaultValueResolver(),
        new TypeHintContainerResolver($container)
        ]);
    }
}
