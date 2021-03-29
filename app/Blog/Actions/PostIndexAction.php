<?php

namespace App\Blog\Actions;

use App\Blog\Models\Posts;
use App\Blog\Models\Categories;
use Framework\Router\Annotation\Route;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @Route("/blog", name="blog.index")
 */
class PostIndexAction
{

    /**
     * Undocumented variable
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     * Undocumented function
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
     * @return string
     */
    public function __invoke(Request $request): string
    {
        $params = $request->getQueryParams();
        // Init Query
        $posts = Posts::setPaginatedQuery(Posts::findPublic())
                ::paginate(12, $params['p'] ?? 1);
        $categories = Categories::find('all');

        return $this->renderer->render('@blog/index', compact('posts', 'categories'));
    }
}
