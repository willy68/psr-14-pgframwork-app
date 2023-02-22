<?php

namespace App\Admin\Actions\Blog;

use App\Entity\Post;
use App\Blog\PostUpload;
use App\Entity\Category;
use DateTimeImmutable;
use Mezzio\Router\RouterInterface;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Controller\CrudController;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostCrudController extends CrudController
{
    protected $viewPath = '@admin/blog/posts';

    protected $routePrefix = 'admin.blog';

    protected $entity = Post::class;

    protected $postUpload;

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
        FlashService $flash,
        PostUpload $postUpload
    ) {
        parent::__construct($renderer, $om, $router, $flash);
        $this->postUpload = $postUpload;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function delete(ServerRequestInterface $request): string|ResponseInterface
    {
        /** @var Post $post */
        $post = $this->em->find($this->entity, $request->getAttribute('id'));
        $this->postUpload->delete($post->getImage());
        $this->em->remove($post);
        $this->em->flush();
        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        $em = $this->om->getManagerForClass(Category::class);
        $qb = $em->createQueryBuilder();
        $qb->select(['u.id', 'u.name'])
            ->from(Category::class, 'u');
        $results = $qb->getQuery()->execute();
        $list = [];
        foreach ($results as $result) {
            $list[$result['id']] = $result['name'];
        }
        $params['categories'] = $list;
        return $params;
    }

    /**
     * @return Post
     */
    protected function getNewEntity(): Post
    {
        $post = new Post();
        $post->setCreatedAt(new DateTimeImmutable('now'));
        return $post;
    }

    /**
     * @param ServerRequestInterface $request
     * @param null $item
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, $item = null): array
    {
        $params = array_merge($request->getParsedBody(), $request->getUploadedFiles());
        if (isset($params['delete']) && $params['delete'] == 1) {
            $this->postUpload->delete($item->getImage());
            $params['image'] = "";
        } elseif ($item) {
            // Upload du fichier
            $image = $this->postUpload->upload($params['image'], $item->getImage());
            if ($image) {
                $params['image'] = $image;
            } else {
                unset($params['image']);
            }
        }

        $params = array_filter($params, function ($key) {
            return in_array($key, ['name', 'slug', 'content', 'created_at', 'category_id', 'image', 'published']);
        }, ARRAY_FILTER_USE_KEY);

        if ($item && $params['category_id'] !== ($item->getCategory() ? $item->getCategory()->getId() : null)) {
            $category = $this->om
                ->getManagerForClass(Category::class)
                ->find(Category::class, $params['category_id']);
            $params['category'] = $category;
            unset($params['category_id']);
        }
        return array_merge($params, [
            'updated_at' => new DateTimeImmutable('now')
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        $validator = parent::getValidator($request)
            ->required('name', 'slug', 'content', 'created_at', 'category_id')
            ->addRules([
                'content' => 'min:2',
                'name'    => 'range:2,250',
                'slug'    => 'slug|range:2,100',
                'created_at' => 'date:Y-m-d H:i:s',
                'image'   => 'filetype:[jpg,png]',
                'category_id' => 'exists:' . Category::class
            ]);
        //if (is_null($request->getAttribute('id'))) {
        //    $validator->uploaded('image');
        //}
        return $validator;
    }
}
