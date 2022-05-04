<?php

namespace App\Blog;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Admin\AdminWidgetInterface;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Renderer\RendererInterface;

class BlogAdminWidget implements AdminWidgetInterface
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(RendererInterface $renderer, EntityManagerInterface $em)
    {
        $this->renderer = $renderer;
        $this->em = $em;
    }

    public function render(): string
    {
        /** @var PostRepository */
        $repo = $this->em->getRepository(Post::class);
        $count = $repo->count([]);
        return $this->renderer->render('@blog/admin/widget', compact('count'));
    }

    public function renderMenu(): string
    {
        return $this->renderer->render('@blog/admin/menu');
    }
}
