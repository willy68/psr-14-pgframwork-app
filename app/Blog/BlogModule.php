<?php

namespace App\Blog;

use App\Blog\Actions\CategoryIndexAction;
use App\Blog\Actions\CategoryShowAction;
use App\Blog\Actions\PostIndexAction;
use App\Blog\Actions\PostShowAction;
use PgFramework\Module;
use PgFramework\Renderer\TwigRenderer;
use PgFramework\Renderer\RendererInterface;

class BlogModule extends Module
{
  /**
   *
   */
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const MIGRATIONS = __DIR__ . '/db/migrations';

    public const SEEDS = __DIR__ . '/db/seeds';

    public const ANNOTATIONS = [
        CategoryIndexAction::class,
        CategoryShowAction::class,
        PostIndexAction::class,
        PostShowAction::class
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
