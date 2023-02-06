<?php

namespace App\Auth;

use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class AuthModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public const ANNOTATIONS = [
        __DIR__ . '/Actions'
    ];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('auth', __DIR__ . '/views');
    }
}
