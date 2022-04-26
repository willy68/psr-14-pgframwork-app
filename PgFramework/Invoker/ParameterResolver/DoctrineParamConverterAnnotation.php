<?php

declare(strict_types=1);

namespace PgFramework\Invoker\ParameterResolver;

use ActiveRecord\Exceptions\RecordNotFound;
use Doctrine\Persistence\ManagerRegistry;
use Invoker\ParameterResolver\ParameterResolver;

class DoctrineParamConverterAnnotation implements ParameterResolver
{
    /**
     * Nom du paramètre de la methode à injecter
     *
     * @var string
     */
    private $methodParam;

    /**
     * Other field to find Record
     *
     * @var array
     */
    private $findBy;

    /**
     *
     * @var ManagerRegistry
     */
    private $mg;

    public function __construct(ManagerRegistry $mg, string $methodParam, array $findBy)
    {
        $this->mg = $mg;
        $this->methodParam = $methodParam;
        $this->findBy = $findBy;
    }

    public function getParameters(
        \ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {

        if (empty($this->findBy)) {
            return $resolvedParameters;
        }

        /** @var \ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (!empty($resolvedParameters)) {
            $reflectionParameters = array_diff_key($reflectionParameters, $resolvedParameters);
        }

        /** @todo best annotation parse */
        $findByKey = array_key_first($this->findBy);

        foreach ($providedParameters as $key => $parameter) {
            if (is_int($key)) {
                continue;
            }

            if ($key === $this->findBy[$findByKey]) {
                /** @var \ReflectionParameter[] $reflectionParameters */
                foreach ($reflectionParameters as $index => $reflectionParameter) {
                    $name = $reflectionParameter->getName();

                    if ($name === $this->methodParam) {
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
                        if (null === ($em = $this->mg->getManagerForClass($class))) {
                            continue;
                        }
                        $repo = $em->getRepository($class);
                        if ($findByKey === 'id') {
                            $obj = $repo->find((int) $parameter);
                        } else {
                            $obj = $repo->findOneBy([$findByKey => $parameter]);
                        }
                        if (!$obj) {
                            throw new RecordNotFound("Couldn't find $class with $findByKey=$parameter");
                        }
                        $resolvedParameters[$index] = $obj;
                    }
                }
            }
        }
        return $resolvedParameters;
    }
}
