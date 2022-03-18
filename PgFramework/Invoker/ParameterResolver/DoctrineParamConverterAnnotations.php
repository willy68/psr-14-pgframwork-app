<?php

namespace PgFramework\Invoker\ParameterResolver;

use Doctrine\ORM\EntityManager;
use Invoker\ParameterResolver\ParameterResolver;
use PgFramework\Invoker\Exception\InvalidAnnotation;
use PgFramework\Invoker\Annotation\ParameterConverter;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;
use PgFramework\Annotation\AnnotationReaderTrait;

class DoctrineParamConverterAnnotations implements ParameterResolver
{
    use AnnotationReaderTrait;

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
     * Get annotation method
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    private function getMethodAnnotation(\ReflectionMethod $method): array
    {
        // Look for @ParameterConverter annotation
        try {
            $annotations = $this->getReader()
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
