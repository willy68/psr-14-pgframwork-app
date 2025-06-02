<?php

namespace PgFramework\Invoker\ParameterResolver;

use Invoker\ParameterResolver\ParameterResolver;
use ReflectionFunctionAbstract;

class AssociativeArrayTypeHintResolver implements ParameterResolver
{

    /**
     * @inheritDoc
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
            if (array_key_exists($parameter->name, $providedParameters)) {
                $resolvedParam = $parameter->getType()->getName() === 'int' &&
                    is_numeric($providedParameters[$parameter->name]) ?
                    $providedParameters[$parameter->name] + 0 :
                    $providedParameters[$parameter->name];
                $resolvedParameters[$index] = $resolvedParam;
            }
        }

        return $resolvedParameters;
    }
}