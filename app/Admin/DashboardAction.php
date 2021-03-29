<?php

namespace App\Admin;

use Framework\Router\Annotation\Route;
use Framework\Renderer\RendererInterface;

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
