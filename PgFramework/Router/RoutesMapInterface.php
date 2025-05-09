<?php

declare(strict_types=1);

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RoutesMapInterface
{
    /**
     * @return array of the format [[$listeners1, $listeners2]]
     */
    public function getListeners(ServerRequestInterface $request): array;
}
