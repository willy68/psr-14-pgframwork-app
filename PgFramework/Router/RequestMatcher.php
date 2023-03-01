<?php

declare(strict_types=1);

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

use function in_array;

class RequestMatcher implements RequestMatcherInterface
{
    protected ?string $path;
    protected array $methods = [];
    protected ?string $host;
    protected array $schemes = [];
    protected ?string $port;

    public function __construct(
        string $path = null,
        array $methods = null,
        string $host = null,
        array $schemes = null,
        string $port = null
    ) {
        $this->setPath($path);
        $this->setMethod($methods);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setPort($port);
    }

    public function match(ServerRequestInterface $request): bool
    {
        if (!empty($this->schemes) && !in_array($request->getUri()->getScheme(), $this->schemes, true)) {
            return false;
        }

        if (!empty($this->methods) && !in_array($request->getMethod(), $this->methods, true)) {
            return false;
        }

        if (null !== $this->path && !preg_match('{' . $this->path . '}', rawurldecode($request->getUri()->getPath()))) {
            return false;
        }

        if (null !== $this->host && !preg_match('{' . $this->host . '}i', $request->getUri()->getHost())) {
            return false;
        }

        if (null !== $this->port && 0 < $this->port && $request->getUri()->getPort() !== $this->port) {
            return false;
        }

        return true;
    }

    /**
     * Set the value of path
     *
     * @param string|null $path
     * @return  self
     */
    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the value of method
     *
     * @param $method
     * @return  self
     */
    public function setMethod($method): static
    {
        $this->methods = null !== $method ? array_map('strtoupper', (array) $method) : [];

        return $this;
    }

    /**
     * Set the value of host
     *
     * @param string|null $host
     * @return  self
     */
    public function setHost(?string $host): static
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set the value of scheme
     *
     * @param $scheme
     * @return  self
     */
    public function setSchemes($scheme): static
    {
        $this->schemes = null !== $scheme ? array_map('strtolower', (array) $scheme) : [];

        return $this;
    }

    /**
     * Set the value of port
     *
     * @param string|null $port
     * @return  self
     */
    public function setPort(?string $port): static
    {
        $this->port = $port;

        return $this;
    }
}
