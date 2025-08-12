<?php

namespace App\Admin\Actions\Blog;

use App\Blog\Models\Categories;
use App\Blog\Table\CategoryTable;
use PgFramework\Actions\CrudAction;
use PgFramework\Validator\Validator;
use Pg\Router\RouterInterface;
use PgFramework\Session\FlashService;
use PgFramework\Renderer\RendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryCrudAction extends CrudAction
{
    protected string $viewPath = '@admin/blog/categories';

    protected string $routePrefix = 'admin.blog.category';

    protected string $model = Categories::class;

    public function __construct(
        RendererInterface $renderer,
        RouterInterface $router,
        CategoryTable $table,
        FlashService $flash
    ) {
        parent::__construct($renderer, $router, $table, $flash);
    }

    /**
     * @param Request $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(Request $request, mixed $item = null): array
    {
        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug']);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param Request $request
     * @return Validator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getValidator(Request $request): Validator
    {
        return parent::getValidator($request)
            ->addRules([
                'name' => 'required|range:2,250',
                'slug' => 'required|range:2,250|slug|unique:App\Blog\Models\Categories,slug,' .
                    $request->getAttribute('id')
            ]);
            /*
            ->required('name', 'slug')
            ->length('name', 2, 250)
            ->length('slug', 2, 250)
            ->unique(
                'slug',
                $this->model::table_name(),
                $this->model::connection()->connection,
                $request->getAttribute('id')
            )
            ->slug('slug');*/
    }
}
