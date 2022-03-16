<?php

namespace PgFramework\Router\Annotation;

use Attribute;
use Doctrine\ORM\Mapping\Annotation;
use PgFramework\Router\Annotation\Exception\RouteAnnotationException;

/**
 *
 * Ex: @Route("/route/{id:\d+}", name="path.route", methods={"GET"})
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Route implements Annotation
{
    private $parameters = [];

    private $path;
    private $name;
    private $host;
    private $methods = [];
    private $schemes = [];

    public function __construct($parameters = [])
    {
        // Method param name
        if (!isset($parameters['value'])) {
            throw new RouteAnnotationException(sprintf(
                '@Route("/route/{id:\d+}", name="path.route",
                methods={"GET"}) expects first parameter "path", %s given.',
                json_encode($parameters)
            ));
        }
        $this->parameters = $parameters;

        $this->path = $parameters['value'];
        $this->name = $parameters['name'] ?? null;
        $this->host = $parameters['host'] ?? null;
        $this->methods = $parameters['methods'] ?? null;
        $this->schemes = $parameters['schemes'] ?? null;
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
}
