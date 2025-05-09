<?php

namespace App\Blog\Actions;

use App\Entity\Category;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Response;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Route("/blog/category/{slug:[a-z\-0-9]+}", name="blog.category")
 */
#[Route('/blog/category/{slug:[a-z\-0-9]+}', name: 'blog.category', methods: ['GET'])]
class CategoryIndexAction
{
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param ServerRequestInterface $request
     * @param EntityManagerInterface $em
     * @return string|ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, EntityManagerInterface $em): ResponseInterface|string
    {
        /** @var CategoryRepository $repo */
        $repo = $em->getRepository(Category::class);
        /** @var Category $category */
        $category = $repo->findOneBy(['slug' => $request->getAttribute('slug')]);
        if (null === $category) {
            return new Response(404, [], $this->renderer->render(
                'error404',
                ['message' => 'Impossible de trouver cette catégorie: ' . $request->getAttribute('slug')]
            ));
        }
        $params = $request->getQueryParams();
        // Init Query
        /** @var PostRepository $postRepo */
        $postRepo = $em->getRepository(Post::class);
        $posts = $postRepo->buildFindPublicForCategory($category->getId())->paginate(12, $params['p'] ?? 1);
        $categories = $repo->findAll();
        $page = $params['p'] ?? 1;

        return $this->renderer->render('@blog/index', compact('posts', 'categories', 'category', 'page'));
    }
}
