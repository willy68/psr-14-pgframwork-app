<?php

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RequestMatcherInterface
{
    /**
     * Match a request
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function match(ServerRequestInterface $request): bool;
}
