<?php

declare(strict_types=1);

namespace App\Blog\Actions;

use App\Entity\Category;
use App\Entity\Post;
use Pg\Router\RouterInterface;
use PgFramework\Router\Annotation\Route;
use PgFramework\Actions\RouterAwareAction;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Invoker\Annotation\ParameterConverter;
use Psr\Http\Message\ResponseInterface;

/**
 * @Route("/blog")
 */
#[Route('/blog')]
class PostShowAction
{
    use RouterAwareAction;

    private RendererInterface $renderer;

    private RouterInterface $router;

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
     * @Route("/{slug:[a-z\-0-9]+}-{id:[0-9]+}", name="blog.show", methods={"GET"})
     *
     * @param string $slug
     * @param Post $post
     * @return string|ResponseInterface
     */
    #[Route('/{slug:[a-z\-]+[a-z0-9]*}-{id:[0-9]+}', name:'blog.show', methods:['GET'])]
    public function __invoke(string $slug, Post $post): string|ResponseInterface
    {
        if ($post->getSlug() !== $slug) {
            return $this->redirect('blog.show', [
                'slug' => $post->getSlug(),
                'id' => $post->getId()
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
    #[Route('/category/{category_id:[0-9]+}/post/{id:[0-9]+}', name:'blog.postShow')]
    #[ParameterConverter('category', options:['id' => 'category_id'])]
    public function postShow(Category $category, Post $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }

    /**
     * Show post with Doctrine
     *
     * @Route("/post/{id:[0-9]+}", name="blog.showPost", methods={"GET"})
     *
     * @param Post $post
     * @return string
     */
    #[Route('/post/{id:[0-9]+}', name:'blog.showPost', methods:['GET'])]
    public function showPost(Post $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }

    /**
     * Show blog post
     *
     * @Route("/category/{category_slug:[a-z\-]+[a-z0-9]*}/post/{id:[0-9]+}", name="blog.postCategoryShow", methods={"GET"})
     * @ParameterConverter("category", options={"slug"="category_slug"})
     *
     * @param Category $category
     * @param Post $post
     * @return string
     */
    #[Route('/category/{category_slug:[a-z\-]+[a-z0-9]*}/post/{id:[0-9]+}', name:'blog.postCategoryShow', methods:['GET'])]
    #[ParameterConverter('category', options:['slug' => 'category_slug'])]
    public function postCategoryShow(Category $category, Post $post): string
    {
        return $this->renderer->render('@blog/show', [
            'post' => $post
        ]);
    }
}
