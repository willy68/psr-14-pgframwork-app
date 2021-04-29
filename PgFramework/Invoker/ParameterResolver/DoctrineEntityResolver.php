<?php

namespace PgFramework\Invoker\ParameterResolver;

use Doctrine\ORM\EntityManager;
use ActiveRecord\Exceptions\RecordNotFound;
use Invoker\ParameterResolver\ParameterResolver;

class DoctrineEntityResolver implements ParameterResolver
{
    /**
     * nom du champ id par défaut id
     *
     * @var string
     */
    private $id = 'id';

    /**
     * Alias pour le champ $id par défaut null
     *
     * @var string|null Si non null sera utilsé à la place de $id
     */
    private $alias;

    /**
     * EntityManager
     *
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param string $key
     * @param string|null $alias
     */
    public function __construct(EntityManager $em, ?string $alias = null)
    {
        $this->em = $em;
        $this->alias = $alias;
    }

    public function getParameters(
        \ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        /** @var \ReflectionParameter[] $reflectionParameters */
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
                    /** @var ReflectionParameter $reflectionParameter */
                    $parameterType = $reflectionParameter->getType();

                    if (!$parameterType) {
                        // No type
                        continue;
                    }
                    /** @var \ReflectionNamedType $parameterType */
                    if ($parameterType->isBuiltin()) {
                        // Primitive types are not supported
                        continue;
                    }
                    if (!$parameterType instanceof \ReflectionNamedType) {
                        // Union types are not supported
                        continue;
                    }

                    $class = $parameterType->getName();

                    try {
                        $repo = $this->em->getRepository($class);
                    } catch (\Exception $e) {
                        continue;
                    }
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
