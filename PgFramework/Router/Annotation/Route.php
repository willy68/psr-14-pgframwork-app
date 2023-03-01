<?php

declare(strict_types=1);

namespace PgFramework\Router\Annotation;

use Attribute;
use Doctrine\ORM\Mapping\MappingAttribute;
use PgFramework\Router\Annotation\Exception\RouteAnnotationException;

use function is_null;
use function is_string;

/**
 *
 * Ex: @Route("/route/{id:\d+}", name="path.route", methods={"GET"}, middlewares={loginMiddleware::class})
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD"})
 *
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Route implements MappingAttribute
{
    private mixed $parameters;

    private mixed $path;
    private mixed $name;
    private mixed $host;
    private mixed $methods;
    private mixed $schemes;
    private mixed $middlewares;

    /**
     * @throws RouteAnnotationException
     */
    public function __construct(
        $parameters = [],
        $path = null,
        string $name = null,
        string $host = null,
        $methods = [],
        $schemes = [],
        $middlewares = []
    ) {
        $this->parameters = $parameters;

        $this->path = $parameters['value'] ?? (is_string($parameters) ? $parameters : $path);
        $this->name = $parameters['name'] ?? (!is_null($name) ? $name : null);
        $this->host = $parameters['host'] ??  (!is_null($host) ? $host : null);
        $this->methods = $parameters['methods'] ?? ([] !== $methods ? $methods : null);
        $this->schemes = $parameters['schemes'] ?? ([] !== $schemes ? $schemes : null);
        $this->middlewares = $middlewares;

        // Method param name
        if (null === $this->path) {
            throw new RouteAnnotationException(sprintf(
                '@Route("/route/{id:\d+}", name="path.route",
                methods={"GET"}) expects first parameter "path", %s given.',
                $this->path
            ));
        }
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the value of path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the value of name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the value of host
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Get the value of methods
     */
    public function getMethods(): ?array
    {
        return $this->methods;
    }

    /**
     * Get the value of schemes
     */
    public function getSchemes(): ?array
    {
        return $this->schemes;
    }

    /**
     * Get the middlewares value
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
