<?php

namespace PgFramework\Invoker\ParameterResolver;

use ReflectionParameter;
use ReflectionFunctionAbstract;
use PgFramework\Annotation\AnnotationsLoader;
use Invoker\ParameterResolver\ParameterResolver;
use PgFramework\Annotation\AnnotationReaderTrait;
use PgFramework\Invoker\Annotation\ParameterConverter;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationConverter;

class ActiveRecordAnnotationsResolver implements ParameterResolver
{
    use AnnotationReaderTrait;

    /**
     *
     * @var AnnotationsLoader
     */
    private $annotationsLoader;

    public function __construct(AnnotationsLoader $annotationsLoader)
    {
        $this->annotationsLoader = $annotationsLoader;
        $this->annotationsLoader->setAnnotation(ParameterConverter::class);
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {

        $annotations = $this->annotationsLoader->getMethodAnnotations($reflection);
        if (empty($annotations)) {
            return $resolvedParameters;
        }

        $converters = $this->parseAnnotation($annotations);

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
     * Parse le tableau d'annotations
     *
     * @param iterable $annotations
     * @return ParameterResolver[]
     */
    protected function parseAnnotation(iterable $annotations): array
    {
        $converters = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof RepeatableAttributeCollection) {
                foreach ($annotation as $annot) {
                    if (!$annot instanceof ParameterConverter) {
                        continue;
                    }
                    $converters[] = new ActiveRecordAnnotationConverter(
                        $annot->getName(),
                        $annot->getOptions()
                    );
                }
            } else {
                if (!$annotation instanceof ParameterConverter) {
                    continue;
                }
                $converters[] = new ActiveRecordAnnotationConverter(
                    $annotation->getName(),
                    $annotation->getOptions()
                );
            }
        }
        return $converters;
    }
}
