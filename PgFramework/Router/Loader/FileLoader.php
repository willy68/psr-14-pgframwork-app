<?php

declare(strict_types=1);

namespace PgFramework\Router\Loader;

use Pg\Router\RouteCollectionInterface;
use \PgFramework\Router\Annotation\Route as AnnotRoute;
use Pg\Router\Route;
use PgFramework\Annotation\AnnotationsLoader;
use PgFramework\Parser\PhpTokenParser;

use ReflectionMethod;

class FileLoader
{
    protected RouteCollectionInterface $collector;

    protected AnnotationsLoader $annotationsLoader;

    public function __construct(
        RouteCollectionInterface $collector,
        AnnotationsLoader $annotationsLoader
    ) {
        if (!\function_exists('token_get_all')) {
            throw new \LogicException("Function token_get_all don't exists in this system");
        }
        $this->collector = $collector;
        $this->annotationsLoader = $annotationsLoader;
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

        /** @var AnnotRoute $classAnnotation*/
        $classAnnotation = $this->annotationsLoader->getClassAnnotation($reflectionClass);

        $routes = [];
        foreach ($reflectionClass->getMethods() as $method) {
			/** @var AnnotRoute $methodAnnotation*/
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
	 * @param AnnotRoute $methodAnnotation
	 * @param ReflectionMethod $method
	 * @param AnnotRoute|null $classAnnotation
	 * @return Route
	 */
    protected function addRoute(
		AnnotRoute $methodAnnotation,
        ReflectionMethod $method,
        ?AnnotRoute $classAnnotation
    ): Route
	{

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
