<?php

namespace Tests\Framework\Modules;

use Mezzio\Router\FastRouteRouter;

class StringModule
{
    public function __construct(FastRouteRouter $router)
    {
        $router->get('/demo', function () {
            return 'DEMO';
        }, 'demo');
    }
}
