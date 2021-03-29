<?php

namespace App\Blog;

use App\Admin\AdminWidgetInterface;
use App\Blog\Models\Posts;
use Framework\Renderer\RendererInterface;

class BlogWidget implements AdminWidgetInterface
{

    /**
     * Undocumented variable
     *
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(): string
    {
        $count = Posts::count();
        return $this->renderer->render('@blog/admin/widget', compact('count'));
    }

    public function renderMenu(): string
    {
        return $this->renderer->render('@blog/menu');
    }
}
