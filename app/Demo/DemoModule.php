<?php

namespace App\Demo;

use Framework\Module;
use Framework\Renderer\RendererInterface;

class DemoModule extends Module
{

    public const ANNOTATIONS = [
        __DIR__ . '/Controller'
    ];

    public function __construct(RendererInterface $renderer)
    {
        $renderer->addPath('demo', __DIR__ . '/views');
    }
}
