<?php

namespace App\Admin\Actions;

use PgFramework\Auth\Middleware\LoggedInMiddleware;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Router\Annotation\Route;

/**
 * @Route("/admin", name="admin", methods={"GET"}, middlawares={LoggedInMiddleware::class})
 */
#[Route('/admin', name:'admin', methods:['GET'], middlewares:[LoggedInMiddleware::class])]
class DashboardAction
{
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return string
     */
    public function __invoke(): string
    {
        return $this->renderer->render('@admin/dashboard');
    }
}
