<?php

namespace App\Blog\Actions;

use App\Blog\Models\Categories;
use App\Blog\Table\CategoryTable;
use Framework\Actions\CrudAction;
use Framework\Validator\Validator;
use Mezzio\Router\RouterInterface;
use Framework\Session\FlashService;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryCrudAction extends CrudAction
{

    protected $viewPath = '@blog/admin/categories';

    protected $routePrefix = 'blog.admin.category';

    /**
     * Class model
     *
     * @var Categories
     */
    protected $model = Categories::class;

    public function __construct(
        RendererInterface $renderer,
        CategoryTable $table,
        RouterInterface $router,
        FlashService $flash
    ) {
        parent::__construct($renderer, $table, $router, $flash);
    }

    /**
     * Undocumented function
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(Request $request, $item = null): array
    {
        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug']);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return Validator
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
