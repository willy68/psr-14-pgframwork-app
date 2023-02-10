<?php

namespace App\Admin\Actions;

use PgFramework\Auth\LoggedInMiddleware;
use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Auth\Middleware\CookieLoginMiddleware;

/**
 */
class DashboardAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     *
     * @Route("/admin", name="admin", methods={"GET"}, middlawares={CookieLoginMiddleware::class,LoggedInMiddleware::class})
     *
     * @return string
     */
    #[Route(
        '/admin',
        name:'admin',
        methods:['GET'],
        middlewares:[CookieLoginMiddleware::class,LoggedInMiddleware::class]
    )]
    public function index(): string
    {
        return $this->renderer->render('@admin/dashboard');
    }
}
