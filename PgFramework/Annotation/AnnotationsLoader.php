<?php

declare(strict_types=1);

namespace PgFramework\Annotation;

use ReflectionClass;
use ReflectionMethod;
use PgFramework\Router\Annotation\Route;
use Doctrine\ORM\Mapping\MappingAttribute;
use Doctrine\ORM\Mapping\Driver\AttributeReader;
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
     * Recherche l'annotation de method pour une classe precise
     *
     * @param \ReflectionMethod $method
     * @return Annotation|null
     */
    public function getMethodAnnotation(ReflectionMethod $method): ?MappingAttribute
    {
        // Look for class annotation
        $reader = $this->getReader();
        if ($reader instanceof AttributeReader) {
            $annotations = $reader->getMethodAttributes($method)[$this->annotationClass] ?? null;
        } else {
            /** @var AnnotationReader */
            $annotations = $reader->getMethodAnnotation($method, $this->annotationClass);
        }

        if ($annotations instanceof RepeatableAttributeCollection) {
            foreach ($annotations as $annot) {
                return $annot;
            }
        }
        return $annotations;
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
        $reader = $this->getReader();
        if ($reader instanceof AttributeReader) {
            $annotations = $reader->getMethodAttributes($method);
        } else {
            $annotations = $reader->getMethodAnnotations($method);
        }

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
    public function getClassAnnotation(ReflectionClass $class): ?MappingAttribute
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for class annotation
        $reader = $this->getReader();
        if ($reader instanceof AttributeReader) {
            $annotation = $reader->getClassAttributes($class)[$this->annotationClass] ?? null;
        } else {
            $annotation = $reader->getClassAnnotation($class, $this->annotationClass);
        }

        if ($annotation instanceof RepeatableAttributeCollection) {
            foreach ($annotation as $annot) {
                return $annot;
            }
        }
        return $annotation;
    }

    /**
     * Recherche les annotations de class
     *
     * @param \ReflectionClass $class
     * @return iterable|null
     */
    public function getClassAnnotations(ReflectionClass $class): ?iterable
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
