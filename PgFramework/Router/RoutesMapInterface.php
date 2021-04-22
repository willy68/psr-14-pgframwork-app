<?php

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RoutesMapInterface
{
    /**
     * @return array of the format [$listeners]
     */
    public function getListeners(ServerRequestInterface $request);
}
