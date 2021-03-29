<?php

namespace App\Blog;

use Framework\Module;
use Framework\Renderer\TwigRenderer;
use Framework\Renderer\RendererInterface;

class BlogModule extends Module
{

  /**
   *
   */
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public const ANNOTATIONS = [
        __DIR__ . '/Actions'
    ];

    /**
     *
     * @param RendererInterface $renderer
     * @param BlogTwigExtension $blogTwigExtension
     */
    public function __construct(
        RendererInterface $renderer,
        BlogTwigExtension $blogTwigExtension
    ) {
        $renderer->addPath('blog', __DIR__ . '/views');
        if ($renderer instanceof TwigRenderer) {
            $renderer->getTwig()->addExtension($blogTwigExtension);
        }
    }
}
