<?php

namespace App\Admin\Actions;

use PgFramework\Auth\LoggedInMiddleware;
use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Auth\Middleware\CookieLoginMiddleware;

/**
 * @Route("/admin", name="admin", methods={"GET"}, middlawares={LoggedInMiddleware::class})
 */
#[Route('/admin', name:'admin', methods:['GET'], middlewares:[LoggedInMiddleware::class])]
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
     * @return string
     */
    public function __invoke(): string
    {
        return $this->renderer->render('@admin/dashboard');
    }
}
