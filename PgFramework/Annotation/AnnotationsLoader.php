<?php

declare(strict_types=1);

namespace PgFramework\Annotation;

use ReflectionClass;
use ReflectionMethod;
use Doctrine\ORM\Mapping\MappingAttribute;
use Doctrine\ORM\Mapping\Driver\AttributeReader;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

class AnnotationsLoader
{
    use AnnotationReaderTrait;

    protected ?string $annotationClass;

    public function __construct(string $annotationClass = null, $reader = null)
    {
        $this->annotationClass = $annotationClass;
        $this->reader = $reader;
    }

    /**
     * Change Annotation class
     *
     * @param string $class
     * @return self
     */
    public function setAnnotation(string $class): static
    {
        $this->annotationClass = $class;
        return $this;
    }

    /**
     * Recherche l’annotation de method pour une classe precise
     *
     * @param ReflectionMethod $method
     * @return MappingAttribute|null
     */
    public function getMethodAnnotation(ReflectionMethod $method): ?MappingAttribute
    {
        // Look for class annotation
        if (null === $this->reader) {
            $this->reader = $this->getReader();
        }
        if ($this->reader instanceof AttributeReader) {
            $annotations = $this->reader->getMethodAttributes($method)[$this->annotationClass] ?? null;
        } else {
            $annotations = $this->reader->getMethodAnnotation($method, $this->annotationClass);
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
     * @param ReflectionMethod $method
     * @return iterable|null
     */
    public function getMethodAnnotations(ReflectionMethod $method): ?iterable
    {
        // Look for class annotation
        if (null === $this->reader) {
            $this->reader = $this->getReader();
        }

        if ($this->reader instanceof AttributeReader) {
            $annotations = $this->reader->getMethodAttributes($method);
        } else {
            $annotations = $this->reader->getMethodAnnotations($method);
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
     * @param ReflectionClass $class
     * @return MappingAttribute|null
     */
    public function getClassAnnotation(ReflectionClass $class): ?MappingAttribute
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for class annotation
        if (null === $this->reader) {
            $this->reader = $this->getReader();
        }

        if ($this->reader instanceof AttributeReader) {
            $annotation = $this->reader->getClassAttributes($class)[$this->annotationClass] ?? null;
        } else {
            $annotation = $this->reader->getClassAnnotation($class, $this->annotationClass);
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
     * @param ReflectionClass $class
     * @return iterable|null
     */
    public function getClassAnnotations(ReflectionClass $class): ?iterable
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for class annotation
        if (null === $this->reader) {
            $this->reader = $this->getReader();
        }

        if ($this->reader instanceof AttributeReader) {
            $annotations = $this->reader->getClassAttributes($class);
        } else {
            $annotations = $this->reader->getClassAnnotations($class);
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
}
