<?php

namespace PgFramework\Kernel;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface KernelInterface
{
    /**
     * Handle request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;

    /**
     * Handle exception
     *
     * @param \Throwable $e
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handleException(\Throwable $e, ServerRequestInterface $request): ResponseInterface;

    /**
     * get Injection Container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;
    /**
     * Set de value of request
     *
     * @param ServerRequestInterface $request
     * @return self
     */
    public function setRequest(ServerRequestInterface $request): self;

    /**
     * get value of request
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface;
}
