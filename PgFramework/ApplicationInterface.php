<?php

declare(strict_types=1);

namespace PgFramework;

use Psr\Container\ContainerInterface;
use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ApplicationInterface
{
    /**
     *
     * @param  ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     *
     * @return KernelInterface|null
     */
    public function getKernel(): ?KernelInterface;
}
