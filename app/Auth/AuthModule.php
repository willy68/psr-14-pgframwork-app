<?php

namespace App\Auth;

use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Auth\Middleware\CookieLogoutMiddleware;
use Mezzio\Router\RouteCollector;

class AuthModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public const ANNOTATIONS = [
        __DIR__ . '/Actions'
    ];

    public function __construct(RendererInterface $renderer, RouteCollector $collector)
    {
        $renderer->addPath('auth', __DIR__ . '/views');

        $route = $collector->getRouteName('auth.logout');
        if ($route) {
            $route->middleware(CookieLogoutMiddleware::class);
        }
    }
}
