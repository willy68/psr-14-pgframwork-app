<?php

namespace App\Demo;

use App\Demo\Controller\DemoController;
use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;

class DemoModule extends Module
{
    public const ANNOTATIONS = [
        DemoController::class
    ];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('demo', __DIR__ . '/views');
    }
}
