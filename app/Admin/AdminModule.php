<?php

namespace App\Admin;

use PgFramework\Module;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\RouterInterface;
use App\Blog\Actions\PostCrudAction;
use PgFramework\Renderer\TwigRenderer;
use App\Blog\Actions\CategoryCrudAction;
use PgFramework\Auth\LoggedInMiddleware;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Auth\Middleware\CookieLoginMiddleware;

class AdminModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const ANNOTATIONS = [
        __DIR__
    ];

    public function __construct(
        RendererInterface $renderer,
        AdminTwigExtension $adminTwigExtension,
        RouterInterface $router,
        string $prefix
    ) {
        $renderer->addPath('admin', __DIR__ . '/views');
        /** @var FastRouteRouter $router */
        $router->crud("$prefix/posts", PostCrudAction::class, 'blog.admin')
            ->middleware(CookieLoginMiddleware::class)
            ->middleware(LoggedInMiddleware::class);
        $router->crud("$prefix/categories", CategoryCrudAction::class, 'blog.admin.category')
            ->middleware(CookieLoginMiddleware::class)
            ->middleware(LoggedInMiddleware::class);
        if ($renderer instanceof TwigRenderer) {
            $renderer->getTwig()->addExtension($adminTwigExtension);
        }
    }
}
