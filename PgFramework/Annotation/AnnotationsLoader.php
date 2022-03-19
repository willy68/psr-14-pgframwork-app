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
     * Recherche la première annotation de method
     *
     * @param \ReflectionMethod $method
     * @return Annotation|null
     */
    public function getMethodAnnotation(ReflectionMethod $method): ?Annotation
    {
        // Look for class annotation
        $annotation = $this->getReader()
            ->getMethodAnnotation(
                $method,
                $this->annotationClass
            );

        if ($annotation instanceof RepeatableAttributeCollection) {
            foreach ($annotation as $annot) {
                return $annot;
            }
        } else {
            return $annotation;
        }
        return null;
    }

    /**
     * Recherche les annotations de method
     *
     * @param \ReflectionMethod $method
     * @return iterable|null
     */
    public function getMethodAnnotations(ReflectionMethod $method): ?iterable
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
     * Recherche la première annotation de class
     *
     * @param \ReflectionClass $class
     * @return Annotation|null
     */
    public function getClassAnnotation(\ReflectionClass $class): ?Annotation
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
                return $annot;
            }
        } else {
            return $annotation;
        }
        return null;
    }

    /**
     * Recherche les annotations de class
     *
     * @param \ReflectionClass $class
     * @return iterable|null
     */
    public function getClassAnnotations(\ReflectionClass $class): ?iterable
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
