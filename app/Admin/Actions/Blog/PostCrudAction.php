<?php

namespace App\Admin\Actions\Blog;

use DateTime;
use Mezzio\Router\RouterInterface;
use App\Blog\PostUpload;
use PgFramework\Validator\Validator;
use App\Blog\Entity\Post;
use App\Blog\Models\Posts;
use App\Blog\Table\PostTable;
use App\Blog\Models\Categories;
use App\Blog\Table\CategoryTable;
use PgFramework\Actions\CrudAction;
use PgFramework\Session\FlashService;
use PgFramework\Renderer\RendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class PostCrudAction extends CrudAction
{
    protected string $viewPath = '@admin/blog/posts';

    protected string $routePrefix = 'admin.blog';

    protected string $model = Posts::class;

    protected CategoryTable $categoryTable;

    private PostUpload $postUpload;

    public function __construct(
        RendererInterface $renderer,
        RouterInterface $router,
        PostTable $table,
        FlashService $flash,
        CategoryTable $categoryTable,
        PostUpload $postUpload
    ) {
        parent::__construct($renderer, $router, $table, $flash);
        $this->categoryTable = $categoryTable;
        $this->postUpload = $postUpload;
    }

    public function delete(Request $request): ResponseInterface
    {
        // $post = $this->table->find($request->getAttribute('id'));
        $post = $this->model::find($request->getAttribute('id'));
        $this->postUpload->delete($post->image);
        $post->delete($request->getAttribute('id'));
        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        // $params['categories'] = $this->categoryTable->findList();
        $params['categories'] = Categories::findList(['id', 'name']);
        return $params;
    }

    protected function getNewEntity(): Post
    {
        $post = new Post();
        $post->createdAt = new DateTime();
        return $post;
    }

    /**
     * @param Request $request
     * @param mixed|null $item
     * @return array
     */
    protected function getParams(Request $request, mixed $item = null): array
    {
        $params = array_merge($request->getParsedBody(), $request->getUploadedFiles());
        if (isset($params['delete']) && $params['delete'] == 1) {
            $this->postUpload->delete($item->image);
            $params['image'] = "";
        } elseif ($item) {
            // Upload du fichier
            $image = $this->postUpload->upload($params['image'], $item->image);
            if ($image) {
                $params['image'] = $image;
            } else {
                unset($params['image']);
            }
        }

        return array_filter($params, function ($key) {
            return in_array($key, ['name', 'slug', 'content', 'created_at', 'category_id', 'image', 'published']);
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
        $validator = parent::getValidator($request)
            ->required('name', 'slug', 'content', 'created_at', 'category_id')
            ->addRules([
                'content' => 'min:2',
                'name'    => 'range:2,250',
                'slug'    => 'slug|range:2,100',
                'created_at' => 'date:Y-m-d H:i:s',
                'image'   => 'filetype:[jpg,png]',
                'category_id' => 'exists:App\Blog\Models\Categories'
            ]);
        if (is_null($request->getAttribute('id'))) {
            $validator->uploaded('image');
        }
        return $validator;
    }
}
