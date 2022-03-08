<?php

namespace PgFramework\Kernel;

use Psr\EventDispatcher\EventDispatcherInterface;
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
     * Get Event Dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface;
    /**
     * Set request value
     *
     * @param ServerRequestInterface $request
     * @return self
     */
    public function setRequest(ServerRequestInterface $request): self;

    /**
     * Get value of request
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface;
}
