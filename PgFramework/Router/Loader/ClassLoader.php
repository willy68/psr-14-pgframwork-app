<?php

namespace PgFramework\Router\Loader;

use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;
use PgFramework\Router\Annotation\Exception\RouteAnnotationException;

class ClassLoader extends MethodLoader
{
    /**
     * Get the annotation class
     *
     * @param \ReflectionClass $class
     * @return object|null
     */
    protected function getClassAnnotation(\ReflectionClass $class): ?object
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for @Route annotation
        try {
            $annotation = $this->getAnnotationReader()
                ->getClassAnnotation(
                    $class,
                    $this->annotationClass
                );
        } catch (\Exception $e) {
            throw new RouteAnnotationException(sprintf(
                '@Route annotation on %s is malformed. %s',
                $class->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        if ($annotation instanceof RepeatableAttributeCollection) {
            foreach ($annotation as $annot) {
                if ($annot instanceof $this->annotationClass) {
                    return $annot;
                }
            }
        } elseif ($annotation instanceof $this->annotationClass) {
            return $annotation;
        }

        return null;
    }
}
