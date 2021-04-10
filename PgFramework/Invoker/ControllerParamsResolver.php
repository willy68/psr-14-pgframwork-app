<?php

namespace PgFramework\Invoker;

use ReflectionParameter;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\Exception\NotEnoughParametersException;
use ReflectionFunctionAbstract;

class ControllerParamsResolver extends ResolverChain
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        $params = parent::getParameters($reflection, $providedParameters, $resolvedParameters);

        // Sort by array key because call_user_func_array ignores numeric keys
        ksort($params);

        // Check all parameters are resolved
        $diff = array_diff_key($reflection->getParameters(), $params);
        $parameter = reset($diff);
        if ($parameter && \assert($parameter instanceof ReflectionParameter) && !$parameter->isVariadic()) {
            throw new NotEnoughParametersException(sprintf(
                'Unable to invoke the callable because no value was given for parameter %d ($%s)',
                $parameter->getPosition() + 1,
                $parameter->name
            ));
        }
        return $params;
    }
}
