<?php

namespace App\Admin;

use App\Admin\Actions\Blog\CategoryCrudController;
use App\Admin\Actions\Blog\PostCrudController;
use App\Admin\Actions\DashboardAction;
use PgFramework\Auth\Middleware\LoggedInMiddleware;
use PgFramework\Module;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Renderer\TwigRenderer;
use PgRouter\RouteCollectionInterface;
use PgRouter\RouteCollector;

class AdminModule extends Module
{
    public const DEFINITIONS = __DIR__ . '/config.php';

    public const ANNOTATIONS = [
        DashboardAction::class
    ];

    public function __construct(
        RendererInterface $renderer,
        AdminTwigExtension $adminTwigExtension,
        RouteCollectionInterface $router,
        string $prefix
    ) {
        $renderer->addPath('admin', __DIR__ . '/views');

        /** @var RouteCollector $router*/
        $router->crud("$prefix/posts", PostCrudController::class, 'admin.blog')
            ->middleware(LoggedInMiddleware::class);
        $router->crud("$prefix/categories", CategoryCrudController::class, 'admin.blog.category')
            ->middleware(LoggedInMiddleware::class);
        if ($renderer instanceof TwigRenderer) {
            $renderer->getTwig()->addExtension($adminTwigExtension);
        }
    }
}
