<?php

namespace App\Blog\Actions;

use App\Entity\Post;
use App\Entity\Category;
use GuzzleHttp\Psr7\Response;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Database\Doctrine\PaginatedQueryBuilder;

/**
 * @Route("/blog/category/{slug:[a-z\-0-9]+}", name="blog.category")
 */
#[Route('/blog/category/{slug:[a-z\-0-9]+}', name:'blog.category', methods:['GET'])]
class CategoryIndexAction
{
    /**
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     *
     * @param Request $request
     * @return string|Response
     */
    public function __invoke(ServerRequestInterface $request, EntityManagerInterface $em)
    {
        /** @var CategoryRepository */
        $repo = $em->getRepository(Category::class);
        /** @var Category */
        $category = $repo->findOneBy(['slug' => $request->getAttribute('slug')]);
        if ($category) {
            $params = $request->getQueryParams();
            // Init Query
            /** @var PostRepository */
            $postRepo = $em->getRepository(Post::class);
            /** @var PaginatedQueryBuilder */
            $builder =  $postRepo->buildFindPublicForCategory($category->getId());
            $posts = $builder->paginate(12, $params['p'] ?? 1);
            $categories = $repo->findAll();
            $page = $params['p'] ?? 1;

            return $this->renderer->render('@blog/index', compact('posts', 'categories', 'category', 'page'));
        } else {
            return new Response(404, [], $this->renderer->render(
                'error404',
                ['message' => 'Impossible de trouver cette categorie: ' . $request->getAttribute('slug')]
            ));
        }
    }
}
