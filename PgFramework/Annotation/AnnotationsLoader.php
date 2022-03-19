<?php

namespace PgFramework\Annotation;

use ReflectionMethod;
use Doctrine\ORM\Mapping\Annotation;
use PgFramework\Router\Annotation\Route;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

class AnnotationsLoader
{
    use AnnotationReaderTrait;

    protected $annotationClass;

    public function __construct(string $annotationClass = null, $reader = null)
    {
        $this->annotationClass = $annotationClass ?? Route::class;
        $this->reader = $reader;
    }

    /**
     * Change Annotation class
     *
     * @param string $class
     * @return void
     */
    public function setAnnotation(string $class)
    {
        $this->annotationClass = $class;
    }

    /**
     * Recherche les annotations de class
     *
     * @param \ReflectionMethod $method
     * @return iterable|Annotation|null
     */
    protected function getMethodAnnotation(ReflectionMethod $method)
    {
        // Look for class annotation
        $annotation = $this->getReader()
            ->getMethodAnnotation(
                $method,
                $this->annotationClass
            );

        if ($annotation instanceof RepeatableAttributeCollection) {
            foreach ($annotation as $annot) {
                yield $annot;
            }
        } else {
            yield $annotation;
        }
        return null;
    }

    /**
     * Recherche les annotations de class
     *
     * @param \ReflectionMethod $method
     * @return iterable|null
     */
    protected function getMethodAnnotations(ReflectionMethod $method): ?iterable
    {
        // Look for class annotation
        $annotations = $this->getReader()
            ->getMethodAnnotations(
                $method
            );

        foreach ($annotations as $annotation) {
            if ($annotation instanceof RepeatableAttributeCollection) {
                foreach ($annotation as $annot) {
                    if ($annot instanceof $this->annotationClass) {
                        yield $annot;
                    }
                }
            } elseif ($annotation instanceof $this->annotationClass) {
                yield $annotation;
            }
        }
        return null;
    }

    /**
     * Get the annotation class
     *
     * @param \ReflectionClass $class
     * @return iterable|Annotation|null
     */
    protected function getClassAnnotation(\ReflectionClass $class)
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for class annotation
        $annotation = $this->getReader()
            ->getClassAnnotation(
                $class,
                $this->annotationClass
            );

        if ($annotation instanceof RepeatableAttributeCollection) {
            foreach ($annotation as $annot) {
                yield $annot;
            }
        } else {
            return $annotation;
        }
        return null;
    }

    /**
     * Get the annotation class
     *
     * @param \ReflectionClass $class
     * @return iterable|null
     */
    protected function getClassAnnotations(\ReflectionClass $class): ?iterable
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for class annotation
        $annotations = $this->getReader()
            ->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof RepeatableAttributeCollection) {
                foreach ($annotation as $annot) {
                    if ($annot instanceof $this->annotationClass) {
                        yield $annot;
                    }
                }
            } elseif ($annotation instanceof $this->annotationClass) {
                yield $annotation;
            }
        }
        return null;
    }
}
