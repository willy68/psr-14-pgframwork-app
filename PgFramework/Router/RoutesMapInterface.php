<?php

declare(strict_types=1);

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RoutesMapInterface
{
    /**
     * @return array of the format [$listeners]
     */
    public function getListeners(ServerRequestInterface $request);
}
