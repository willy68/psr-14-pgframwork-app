<?php

declare(strict_types=1);

namespace PgFramework\Invoker\Annotation;

use Attribute;
use Doctrine\ORM\Mapping\MappingAttribute;
use PgFramework\Invoker\Exception\InvalidAnnotation;

/**
 * "ParameterConverter" annotation.
 *
 * Marks a method as an injection point
 *
 * First param is the method parameter to convert from route param
 * ```
 * Ex @ParameterConverter("post", options={"id"="post_id"})
 * ```
 *
 * @api
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD"})
 *
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
final class ParameterConverter implements MappingAttribute
{
    /**
     * Parameters, indexed by the parameter number (index) or name.
     *
     * Used if the annotation is set on a method
     * @var array
     */
    private $parameters = [];

    private $name;

    private $options = [];

    /**
     * @throws InvalidAnnotation
     */
    public function __construct($parameters = [], string $name = null, $options = [])
    {
        $this->parameters = $parameters;
        $this->name = $parameters['value'] ?? (\is_string($parameters) ? $parameters : $name);
        $this->options = $parameters['options'] ?? ([] !== $options ? $options : null);

        // Method param name
        if (null === $this->name) {
            throw new InvalidAnnotation(sprintf(
                '@ParameterConverter("name", options={"id" = "value"}) expects parameter "name", %s given.',
                $name
            ));
            return;
        }
    }

    /**
     * @return array Parameters, indexed by the parameter number (index) or name
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of options
     */
    public function getOptions()
    {
        return $this->options;
    }
}
