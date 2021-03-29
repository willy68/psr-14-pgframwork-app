<?php

namespace App\Blog\Actions;

use App\Blog\Models\Posts;
use GuzzleHttp\Psr7\Response;
use App\Blog\Models\Categories;
use Framework\Router\Annotation\Route;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @Route("/blog/category/{slug:[a-z\-0-9]+}", name="blog.category")
 */
class CategoryShowAction
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
    public function __invoke(Request $request)
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
                ['message' => 'Impossible de trouver cette categorie: ' . $request->getAttribute('slug')]
            ));
        }
    }
}
