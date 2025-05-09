<?php

declare(strict_types=1);

namespace PgFramework\Invoker\ParameterResolver;

use ActiveRecord\Exceptions\RecordNotFound;
use Doctrine\Persistence\ManagerRegistry;
use Invoker\ParameterResolver\ParameterResolver;
use ReflectionFunctionAbstract;
use ReflectionNamedType;

class DoctrineEntityResolver implements ParameterResolver
{
    /**
     * Nom du champ id par défaut id
     */
    private string $id = 'id';

    /**
     * Alias pour le champ $id par défaut null
     *
     * @var string|null Si non null sera utilisé à la place de $id
     */
    private ?string $alias;

    private ManagerRegistry $mg;

    /**
     * Constructor
     *
     * @param ManagerRegistry $mg
     * @param string|null $alias
     */
    public function __construct(ManagerRegistry $mg, ?string $alias = null)
    {
        $this->mg = $mg;
        $this->alias = $alias;
    }

    /**
     * @throws RecordNotFound
     */
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

        foreach ($providedParameters as $key => $parameter) {
            if (is_int($key)) {
                continue;
            }

            $id = $this->alias ?: $this->id;

            if ($key === $id) {
                foreach ($reflectionParameters as $index => $reflectionParameter) {
                    $parameterType = $reflectionParameter->getType();

                    if (!$parameterType) {
                        // No type
                        continue;
                    }
                    /** @var ReflectionNamedType $parameterType */
                    if ($parameterType->isBuiltin()) {
                        // Primitive types not supported
                        continue;
                    }
                    if (!$parameterType instanceof ReflectionNamedType) {
                        // Union types not supported
                        continue;
                    }

                    $class = $parameterType->getName();
                    if (null === ($em = $this->mg->getManagerForClass($class))) {
                        continue;
                    }
                    $repo = $em->getRepository($class);
                    $entity = $repo->find($parameter);
                    if ($entity) {
                        $resolvedParameters[$index] = $entity;
                    } else {
                        throw new RecordNotFound("Couldn't find $class with id=$parameter");
                    }
                }
            }
        }
        return $resolvedParameters;
    }
}
