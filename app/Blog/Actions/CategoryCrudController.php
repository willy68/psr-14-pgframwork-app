<?php

namespace App\Blog\Actions;

use App\Entity\Category;
use Mezzio\Router\RouterInterface;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Controller\CrudController;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryCrudController extends CrudController
{
    protected $viewPath = '@blog/admin/categories';

    protected $routePrefix = 'blog.admin.category';

    protected $entity = Category::class;

    /**
     * @param RendererInterface $renderer
     * @param ManagerRegistry $om
     * @param RouterInterface $router
     * @param FlashService $flash
     * @param PostUpload $postUpload
     */
    public function __construct(
        RendererInterface $renderer,
        ManagerRegistry $om,
        RouterInterface $router,
        FlashService $flash
    ) {
        parent::__construct($renderer, $om, $router, $flash);
    }

    /**
     * @param ServerRequestInterface $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, $item = null): array
    {
        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug']);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Undocumented function
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
            ->addRules([
                'name' => 'required|range:2,250',
                'slug' => 'required|range:2,250|slug|unique:' . Category::class . ',slug,' .
                    $request->getAttribute('id')
            ]);
    }
}
