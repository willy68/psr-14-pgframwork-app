<?php

namespace App\Blog;

use Twig\TwigFunction;
use App\Admin\AdminWidgetInterface;
use Twig\Extension\AbstractExtension;

class BlogTwigExtension extends AbstractExtension
{

    private $widgets;

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

    public function renderMenu()
    {
        return array_reduce($this->widgets, function ($html, AdminWidgetInterface $widget) {
            return $html . $widget->renderMenu();
        }, '');
    }
}
