<?php

namespace App\Blog\Actions;

use App\Entity\Post;
use App\Blog\PostUpload;
use App\Entity\Category;
use Doctrine\ORM\EntityManager;
use Mezzio\Router\RouterInterface;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Controller\CrudController;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostCrudController extends CrudController
{
    protected $viewPath = '@blog/admin/posts';

    protected $routePrefix = 'blog.admin';

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

    public function delete(ServerRequestInterface $request)
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
        /** @var EntityManager */
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
    protected function getNewEntity()
    {
        $post = new Post();
        $post->setCreatedAt(new \DateTimeImmutable('now'));
        return $post;
    }

    /**
     * @param Post $item
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, $item = null): array
    {
        $params = array_merge($request->getParsedBody(), $request->getUploadedFiles());
        if (isset($params['delete']) && $params['delete'] == 1) {
            $this->postUpload->delete($item->image);
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
        $params['published'] = isset($params['published']) ? (bool)$params['published'] : false;
        $params['created_at'] = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $params['created_at']);
        return array_merge($params, [
            'updated_at' => new \DateTimeImmutable('now')
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
