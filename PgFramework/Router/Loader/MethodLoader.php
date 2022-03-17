<?php

namespace PgFramework\Router\Loader;

use ReflectionMethod;
use Doctrine\Common\Annotations\Reader;
use PgFramework\Router\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AttributeReader;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;
use PgFramework\Annotation\AnnotationReaderTrait;
use PgFramework\Router\Annotation\Exception\RouteAnnotationException;

class MethodLoader
{
    use AnnotationReaderTrait;

    protected $reader;

    protected $annotationClass = Route::class;

    public function __construct(?Reader $reader = null)
    {
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
     * Recherche les annotations de route
     *
     * @param \ReflectionMethod $method
     * @return iterable|null
     */
    protected function getMethodAnnotations(ReflectionMethod $method): ?iterable
    {
        // Look for @Route annotation
        try {
            $annotations = $this->getReader()
                ->getMethodAnnotations(
                    $method
                );
        } catch (\Exception $e) {
            throw new RouteAnnotationException(sprintf(
                '@Route annotation on %s::%s is malformed. %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $e->getMessage()
            ), 0, $e);
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
