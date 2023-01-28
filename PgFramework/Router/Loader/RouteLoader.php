<?php

namespace PgFramework\Router\Loader;

use ReflectionClass;
use ReflectionMethod;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use PgFramework\Annotation\AnnotationsLoader;
use PgFramework\Router\Annotation\Route as AnnotRoute;

class RouteLoader
{
    protected $collector;

    protected $annotationsLoader;

    public function __construct(
        RouteCollector $collector,
        AnnotationsLoader $annotationsLoader
    ) {
        $this->collector = $collector;
        $this->annotationsLoader = $annotationsLoader;
        $this->annotationsLoader->setAnnotation(AnnotRoute::class);
    }
    /**
     * Parse annotations @Route and add routes to the router
     *
     * @param string $file
     * @return Route[]|null
     */
    public function load(string $className): ?array
    {
        if (!class_exists($className)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->isAbstract()) {
            return null;
        }

        /** @var \PgFramework\Router\Annotation\Route */
        $classAnnotation = $this->annotationsLoader->getClassAnnotation($reflectionClass);

        $routes = [];
        foreach ($reflectionClass->getMethods() as $method) {
            /** @var \PgFramework\Router\Annotation\Route */
            foreach ($this->annotationsLoader->getMethodAnnotations($method) as $methodAnnotation) {
                $routes[] = $this->addRoute($methodAnnotation, $method, $classAnnotation);
            }
        }

        if (empty($routes) && $classAnnotation && $reflectionClass->hasMethod('__invoke')) {
            foreach ($this->annotationsLoader->getClassAnnotations($reflectionClass) as $classAnnotation) {
                $routes[] = $this->collector->route(
                    $classAnnotation->getPath(),
                    $reflectionClass->getName(),
                    $classAnnotation->getName(),
                    $classAnnotation->getMethods()
                )
                    ->setSchemes($classAnnotation->getSchemes())
                    ->middlewares($classAnnotation->getMiddlewares());
            }
        }

        gc_mem_caches();

        if (empty($routes)) {
            return null;
        }

        return $routes;
    }

    /**
     * Add route to router
     *
     * @param AnnotRoute $methodAnnotation
     * @param \ReflectionMethod $method
     * @param AnnotRoute|null $classAnnotation
     * @return Route
     */
    protected function addRoute(
        AnnotRoute $methodAnnotation,
        ReflectionMethod $method,
        ?AnnotRoute $classAnnotation
    ): Route {

        $path = $methodAnnotation->getPath();
        if ($classAnnotation) {
            $path = $classAnnotation->getPath() . $path;
        }
        return $this->collector->route(
            $path,
            $method->getDeclaringClass()->getName() . "::" . $method->getName(),
            $methodAnnotation->getName(),
            $methodAnnotation->getMethods()
        )
            ->setSchemes($methodAnnotation->getSchemes())
            ->middlewares($methodAnnotation->getMiddlewares());
    }
}
