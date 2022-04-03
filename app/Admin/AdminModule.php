<?php

namespace App\Admin;

use PgFramework\Module;
use Mezzio\Router\RouteCollector;
use App\Blog\Actions\PostCrudAction;
use PgFramework\Renderer\TwigRenderer;
use App\Blog\Actions\CategoryCrudAction;
use PgFramework\Auth\LoggedInMiddleware;
use Mezzio\Router\RouteCollectionInterface;
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
        RouteCollectionInterface $router,
        string $prefix
    ) {
        $renderer->addPath('admin', __DIR__ . '/views');

        /** @var RouteCollector $router*/
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
