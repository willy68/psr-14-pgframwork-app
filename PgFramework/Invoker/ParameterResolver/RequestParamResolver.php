<?php

declare(strict_types=1);

namespace PgFramework\Invoker\ParameterResolver;

use Invoker\ParameterResolver\ParameterResolver;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

class RequestParamResolver implements ParameterResolver
{
    public function __construct(private ServerRequestInterface $request)
    {
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        $reflectionParameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (!empty($resolvedParameters)) {
            $reflectionParameters = array_diff_key($reflectionParameters, $resolvedParameters);
        }

        foreach ($reflectionParameters as $index => $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            if (!$parameterType) {
                // No type
                continue;
            }
            if (!$parameterType instanceof ReflectionNamedType) {
                // Union types not supported
                continue;
            }
            if ($parameterType->isBuiltin()) {
                // Primitive types not supported
                continue;
            }

            $class = $parameterType->getName();
            if ($class === ServerRequestInterface::class) {
                $resolvedParameters[$index] = $this->request;
            }
        }
        return $resolvedParameters;
    }
}
