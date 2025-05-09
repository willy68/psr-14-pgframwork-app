<?php

namespace App\Blog\Actions;

use ActiveRecord\Exceptions\RecordNotFound;
use App\Blog\Models\Categories;
use App\Blog\Models\Posts;
use GuzzleHttp\Psr7\Response;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @Route("/blog/dcategory/{slug:[a-z\-0-9]+}", name="blog.dcategory")
 */
#[Route('/blog/dcategory/{slug:[a-z\-0-9]+}', name: 'blog.dcategory', methods: ['GET'])]
class CategoryShowAction
{
    private RendererInterface $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     *
     * @param Request $request
     * @return string|ResponseInterface
     * @throws RecordNotFound
     */
    public function __invoke(Request $request): string|ResponseInterface
    {
        $category = Categories::find_by_slug($request->getAttribute('slug'));
        if ($category) {
            $params = $request->getQueryParams();
            // Init Query
            $posts = Posts::setPaginatedQuery(Posts::findPublicForCategory($category->id))
                ::paginate(12, $params['p'] ?? 1);
            $categories = Categories::find('all');
            $page = $params['p'] ?? 1;

            return $this->renderer->render('@blog/index', compact('posts', 'categories', 'category', 'page'));
        } else {
            return new Response(404, [], $this->renderer->render(
                'error404',
                ['message' => 'Impossible de trouver cette catégorie: ' . $request->getAttribute('slug')]
            ));
        }
    }
}
