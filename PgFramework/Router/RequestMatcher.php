<?php

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

class RequestMatcher implements RequestMatcherInterface
{
    protected $path;
    protected $method = [];
    protected $host;
    protected $schemes = [];
    protected $port;

    public function __construct(
        string $path = null,
        array $method = null,
        string $host = null,
        string $scheme = null,
        string $port = null
    ) {
        $this->setPath($path);
        $this->setMethod($method);
        $this->setHost($host);
        $this->setSchemes($scheme);
        $this->setPort($port);
    }

    public function match(ServerRequestInterface $request): bool
    {
        if (!empty($this->schemes) && !\in_array($request->getUri()->getScheme(), $this->schemes, true)) {
            return false;
        }

        if (!empty($this->methods) && !\in_array($request->getMethod(), $this->methods, true)) {
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
     * @return  self
     */
    public function setPath(?string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the value of method
     *
     * @return  self
     */
    public function setMethod($method)
    {
        $this->methods = null !== $method ? array_map('strtoupper', (array) $method) : [];

        return $this;
    }

    /**
     * Set the value of host
     *
     * @return  self
     */
    public function setHost(?string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set the value of scheme
     *
     * @return  self
     */
    public function setSchemes($scheme)
    {
        $this->schemes = null !== $scheme ? array_map('strtolower', (array) $scheme) : [];

        return $this;
    }

    /**
     * Set the value of port
     *
     * @return  self
     */
    public function setPort(?string $port)
    {
        $this->port = $port;

        return $this;
    }
}
