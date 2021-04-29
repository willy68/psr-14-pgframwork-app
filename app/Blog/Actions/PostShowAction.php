<?php

namespace App\Blog\Actions;

use App\Entity\Category;
use App\Entity\Post;
use Mezzio\Router\RouterInterface;
use PgFramework\Router\Annotation\Route;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Invoker\Annotation\ParameterConverter;

/**
 * @Route("/blog")
 */
class PostShowAction
{
    use RouterAwareAction;

    /**
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructeur
     *
     * @param RendererInterface $renderer
     */
    public function __construct(
        RendererInterface $renderer,
        RouterInterface $router
    ) {
        $this->renderer = $renderer;
        $this->router = $router;
    }

    /**
     * Show blog post
     *
     * @Route("/{slug:[a-z\-0-9]+}-{id:[0-9]+}", name="blog.show", method={"GET"})
     *
     * @param string $slug
     * @param Post $post
     * @return mixed
     */
    public function __invoke(string $slug, Post $post)
    {
        if ($post->getSlug() !== $slug) {
            return $this->redirect('blog.show', [
                'slug' => $post->slug,
                'id' => $post->id
            ]);
        }

        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }

    /**
     * Show blog post
     *
     * @Route("/category/{category_id:[0-9]+}/post/{id:[0-9]+}", name="blog.postShow")
     * @ParameterConverter("category", options={"id"="category_id"})
     *
     * @param Category $category
     * @param Post $post
     * @return string
     */
    public function postShow(Category $category, Post $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }

    /**
     * Show post with Doctrine
     * 
     * @Route("/post/{id:[0-9]+}", name="blog.showPost", method={"GET"})
     *
     * @param Post $post
     * @return string
     */
    public function showPost(Post $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }

    /**
     * Show blog post
     *
     * @Route("/category/{category_slug:[a-z\-0-9]+}/post/{id:[0-9]+}", name="blog.postCategoryShow", methods={"GET"})
     * @ParameterConverter("category", options={"slug"="category_slug"})
     *
     * @param Category $category
     * @param Post $post
     * @return string
     */
    public function postCategoryShow(Category $category, Post $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }
}
