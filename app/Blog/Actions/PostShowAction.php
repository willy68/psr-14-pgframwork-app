<?php

namespace App\Blog\Actions;

use App\Blog\Models\Posts;
use App\Blog\Models\Categories;
use Mezzio\Router\RouterInterface;
use Framework\Router\Annotation\Route;
use Framework\Actions\RouterAwareAction;
use Framework\Renderer\RendererInterface;
use Framework\Invoker\Annotation\ParameterConverter;

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
    public function __invoke(string $slug, Posts $post)
    {
        if ($post->slug !== $slug) {
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
     * @param Categories $category
     * @param Posts $post
     * @return string
     */
    public function postShow(Categories $category, Posts $post): string
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
     * @param Categories $category
     * @param Posts $post
     * @return string
     */
    public function postCategoryShow(Categories $category, Posts $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }
}
