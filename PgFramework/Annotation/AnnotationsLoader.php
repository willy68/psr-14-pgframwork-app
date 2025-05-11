<?php

declare(strict_types=1);

namespace PgFramework\Annotation;

use Doctrine\Common\Annotations\Reader;
use PgFramework\Router\Annotation\Route;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;

use ReflectionClass;
use ReflectionMethod;

class AnnotationsLoader
{
    use AnnotationReaderTrait;

    protected string $annotationClass;

    public function __construct(string $annotationClass = null, Reader $reader = null)
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
    public function setAnnotation(string $class): void
	{
        $this->annotationClass = $class;
    }

	/**
	 * Recherche la première annotation de method
	 *
	 * @param ReflectionMethod $method
	 * @return object|null
	 */
    public function getMethodAnnotation(ReflectionMethod $method):?object
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
     * @param ReflectionMethod $method
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
	 * @param ReflectionClass $class
	 * @return object|null
	 */
    public function getClassAnnotation(ReflectionClass $class): ?object
    {
        if ($class->isAbstract()) {
            return null;
        }

        // Look for class annotation
        $reader = $this->getReader();
		$annotation = $reader->getClassAnnotation($class, $this->annotationClass);

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
