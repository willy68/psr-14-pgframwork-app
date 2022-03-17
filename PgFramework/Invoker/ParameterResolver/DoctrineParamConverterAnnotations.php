<?php

namespace PgFramework\Invoker\ParameterResolver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeReader;
use Invoker\ParameterResolver\ParameterResolver;
use Doctrine\Common\Annotations\AnnotationReader;
use PgFramework\Invoker\Exception\InvalidAnnotation;
use PgFramework\Invoker\Annotation\ParameterConverter;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

class DoctrineParamConverterAnnotations implements ParameterResolver
{
    /**
     * Reader
     *
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     *
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getParameters(
        \ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {

        $annotations = $this->getMethodAnnotation($reflection);
        if (empty($annotations)) {
            return $resolvedParameters;
        }

        $converters = $this->parseAnnotation($annotations);

        if (empty($converters)) {
            return $resolvedParameters;
        }

        /** @var \ReflectionParameter[] $reflectionParameters */
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
     * @return  mixed reader The annotation reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader === null) {
            if (PHP_VERSION_ID >= 80000) {
                $this->annotationReader = new AttributeReader();
            } else {
                $this->annotationReader = new AnnotationReader();
            }
        }

        return $this->annotationReader;
    }

    /**
     * Get annotation method
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    private function getMethodAnnotation(\ReflectionMethod $method): array
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
     * @return ParameterResolver[]
     */
    protected function parseAnnotation(array $annotations): array
    {
        $converters = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof RepeatableAttributeCollection) {
                foreach ($annotation as $annot) {
                    if (!$annot instanceof ParameterConverter) {
                        continue;
                    }
                    $converters[] = new DoctrineParamConverterAnnotation(
                        $this->em,
                        $annot->getName(),
                        $annot->getOptions()
                    );
                }
            }
        }
        return $converters;
    }
}
