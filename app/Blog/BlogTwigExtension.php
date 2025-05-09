<?php

namespace App\Blog;

use App\Admin\AdminWidgetInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlogTwigExtension extends AbstractExtension
{
    private array $widgets;

    public function __construct(array $widgets)
    {
        $this->widgets = $widgets;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('blog_menu', [$this, 'renderMenu'], ['is_safe' => ['html']])
        ];
    }

    public function renderMenu(): string
    {
        return array_reduce($this->widgets, function ($html, AdminWidgetInterface $widget) {
            return $html . $widget->renderMenu();
        }, '');
    }
}
