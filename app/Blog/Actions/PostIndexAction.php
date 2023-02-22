<?php

namespace App\Blog\Actions;

use App\Entity\Post;
use App\Blog\Models\Posts;
use App\Blog\Models\Categories;
use App\Entity\Category;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Router\Annotation\Route;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 */
class PostIndexAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Get all public articles with ActiveRecord
     *
     * @Route("/dblog", name="blog.indexAR", methods={"GET"})
     *
     * @param Request $request
     * @return string
     */
    #[Route('/dblog', name:'blog.indexAR', methods:['GET'])]
    public function __invoke(Request $request): string
    {
        $params = $request->getQueryParams();
        // Init Query
        $posts = Posts::setPaginatedQuery(Posts::findPublic())
                ::paginate(12, $params['p'] ?? 1);
        $categories = Categories::find('all');

        return $this->renderer->render('@blog/index', compact('posts', 'categories'));
    }

    /**
     * Get All Articles with Doctrine
     *
     * @Route("/blog", name="blog.index", methods={"GET"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return string
     */
    #[Route('/blog', name:'blog.index', methods:['GET'])]
    public function index(Request $request, EntityManagerInterface $em): string
    {
        $params = $request->getQueryParams();
        /** @var PostRepository $repo*/
        $repo = $em->getRepository(Post::class);
        $posts = $repo->buildFindPublic()->paginate(12, $params['p'] ?? 1);
        $categories = $em->getRepository(Category::class)->findAll();
        return $this->renderer->render('@blog/index', compact('posts', 'categories'));
    }
}
