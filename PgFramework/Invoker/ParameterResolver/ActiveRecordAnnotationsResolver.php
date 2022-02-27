<?php

namespace PgFramework\Invoker\ParameterResolver;

use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;
use Doctrine\Common\Annotations\AnnotationReader;
use PgFramework\Invoker\Exception\InvalidAnnotation;
use PgFramework\Invoker\Annotation\ParameterConverter;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationConverter;

class ActiveRecordAnnotationsResolver implements ParameterResolver
{
    /**
     * Reader
     *
     * @var AnnotationReader
     */
    private $annotationReader;

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {

        $annotations = $this->getMethodAnnotation($reflection);
        if (empty($annotations)) {
            return $resolvedParameters;
        }

        $converters = $this->parseAnnotation($annotations, $reflection);

        if (empty($converters)) {
            return $resolvedParameters;
        }

        /** @var ReflectionParameter[] $reflectionParameters */
        $reflectionParameters = $reflection->getParameters();
        // Skip parameters already resolved
        if (!empty($resolvedParameters)) {
            $reflectionParameters = array_diff_key($reflectionParameters, $resolvedParameters);
        }

        foreach ($converters as $converter) {
            $resolvedParameters = $converter->getParameters($reflection, $providedParameters, $resolvedParameters);

            $diff = array_diff_key($reflectionParameters, $resolvedParameters);
            if (empty($diff)) {
                // Stop traversing: all parameters are resolved
                return $resolvedParameters;
            }
        }

        return $resolvedParameters;
    }

    /**
     * @return AnnotationReader The annotation reader
     */
    public function getAnnotationReader(): AnnotationReader
    {
        if ($this->annotationReader === null) {
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    /**
     * Get annotation method
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    private function getMethodAnnotation(ReflectionMethod $method): array
    {
        // Look for @ParameterConverter annotation
        try {
            $annotations = $this->getAnnotationReader()
                ->getMethodAnnotations($method);
        } catch (\Exception $e) {
            throw new InvalidAnnotation(sprintf(
                '@ParameterConverter annotation on %s::%s is malformed. %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $e->getMessage()
            ), 0, $e);
        }
        return $annotations;
    }

    /**
     * Parse le tableau d'annotations
     *
     * @param array $annotations
     * @param \ReflectionMethod $method
     * @return ParameterResolver[]
     */
    protected function parseAnnotation(array $annotations, ReflectionMethod $method): array
    {
        $converters = [];
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof ParameterConverter) {
                continue;
            }

            $annotationParams = $annotation->getParameters();
            if (!isset($annotationParams["value"]) || !isset($annotationParams["options"])) {
                throw new InvalidAnnotation(sprintf(
                    '@ParameterConverter annotation on %s::%s is malformed.',
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                ));
            }
            $converters[] = new ActiveRecordAnnotationConverter(
                $annotationParams['value'],
                $annotationParams['options']
            );
        }
        return $converters;
    }
}
