<?php

namespace App\Admin;

use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;

/**
 */
class DashboardAction
{

    /**
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     *
     * @var AdminWidgetInterface[]
     */
    private $widgets;

    public function __construct(RendererInterface $renderer, array $widgets)
    {

        $this->renderer = $renderer;
        $this->widgets = $widgets;
    }

    /**
     *
     * @Route("/admin", name="admin", methods={"GET"})
     *
     * @return string
     */
    public function index(): string
    {
        $widgets = array_reduce($this->widgets, function ($html, AdminWidgetInterface $widget) {
            return $html . $widget->render();
        }, '');
        return $this->renderer->render('@admin/dashboard', compact('widgets'));
    }
}
