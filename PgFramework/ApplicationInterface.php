<?php

namespace PgFramework;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ApplicationInterface
{
    /**
     *
     * @param  ServerRequestInterface|null $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface;

    /**
     *
     * @return ContainerInterface
     * @throws Exception
     */
    public function getContainer(): ContainerInterface;
}
