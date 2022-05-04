<?php

namespace App\Admin;

use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;

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
     * @Route("/admin", name="admin", methods={"GET"})
     *
     * @return string
     */
    #[Route('/admin', name:'admin', methods:['GET'])]
    public function index(): string
    {
        return $this->renderer->render('@admin/dashboard');
    }
}
