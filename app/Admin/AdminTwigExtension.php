<?php

namespace App\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminTwigExtension extends AbstractExtension
{
    private array $widgets;

    public function __construct(array $widgets)
    {
        $this->widgets = $widgets;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_menu', [$this, 'renderMenu'], ['is_safe' => ['html']]),
            new TwigFunction('admin_widget', [$this, 'render'], ['is_safe' => ['html']])
        ];
    }

    public function renderMenu(): string
    {
        return array_reduce($this->widgets, function ($html, AdminWidgetInterface $widget) {
            return $html . $widget->renderMenu();
        }, '');
    }

    public function render(): string
    {
        return array_reduce($this->widgets, function ($html, AdminWidgetInterface $widget) {
            return $html . $widget->render();
        }, '');
    }
}
