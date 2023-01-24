<?php

declare(strict_types=1);

namespace PgFramework\Router\Loader;

use ReflectionMethod;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use PgFramework\Parser\PhpTokenParser;
use Doctrine\ORM\Mapping\MappingAttribute;
use PgFramework\Annotation\AnnotationsLoader;
use PgFramework\Router\Annotation\Route as AnnotRoute;

class FileLoader
{
    protected $collector;

    protected $annotationsLoader;

    public function __construct(
        RouteCollector $collector,
        AnnotationsLoader $annotationsLoader
    ) {
        if (!\function_exists('token_get_all')) {
            throw new \LogicException("Function token_get_all don't exists in this system");
        }
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
    public function load(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $class = PhpTokenParser::findClass($file);
        if (!$class || !class_exists($class)) {
            return null;
        }

        $reflectionClass = new \ReflectionClass($class);
        if ($reflectionClass->isAbstract()) {
            return null;
        }

        /** @var \PgFramework\Router\Annotation\Route */
        $classAnnotation = $this->annotationsLoader->getClassAnnotation($reflectionClass);

        $routes = [];
        foreach ($reflectionClass->getMethods() as $method) {
            foreach ($this->annotationsLoader->getMethodAnnotations($method) as $methodAnnotation) {
                $routes[] = $this->addRoute($methodAnnotation, $method, $classAnnotation);
            }
        }

        if (empty($routes) && $classAnnotation && $reflectionClass->hasMethod('__invoke')) {
            $route[] = $this->collector->route(
                $classAnnotation->getPath(),
                $reflectionClass->getName(),
                $classAnnotation->getName(),
                $classAnnotation->getMethods()
            )
                ->setSchemes($classAnnotation->getSchemes());
        }

        gc_mem_caches();
        return $routes;
    }

    /**
     * Add route to router
     *
     * @param object $methodAnnotation
     * @param \ReflectionMethod $method
     * @param object|null $classAnnotation
     * @return Route
     */
    protected function addRoute(
        MappingAttribute $methodAnnotation,
        ReflectionMethod $method,
        ?object $classAnnotation
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
            ->setSchemes($methodAnnotation->getSchemes());
    }
}
