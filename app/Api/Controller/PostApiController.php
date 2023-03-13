<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Blog\PostUpload;
use App\Entity\Category;
use App\Entity\Post;
use App\Repository\PostRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use GuzzleHttp\Psr7\Utils;
use PgFramework\Auth\FailedAccessException;
use PgFramework\Database\Hydrator;
use PgFramework\Database\NoRecordException;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Middleware\BodyParserMiddleware;
use PgFramework\Response\JsonResponse;
use PgFramework\Router\Annotation\Route;
use PgFramework\Security\Authorization\AuthorizationCheckerInterface;
use PgFramework\Validator\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1')]
class PostApiController
{
    private ObjectManager $em;

    public function __construct(
        private ManagerRegistry               $om,
        private SerializerInterface           $serializer,
        private AuthorizationCheckerInterface $authChecker,
        private PostUpload                    $postUpload
    ) {
        $this->em = $om->getManager();
    }

    #[Route('/posts', name: 'api.posts.index', methods: ['GET'])]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $countTotal = $repo->count([]);
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? $countTotal;
        $posts = $repo->findAllForApi()
            ->setFirstResult((int)$offset)
            ->setMaxResults((int)$limit)
            ->getQuery()
            ->getResult();
        if (!$posts) {
            return new JsonResponse(400, 'Request out of range');
        }
        return $this->getResponseForList($posts, $request, (int)$offset, (int)$limit, $countTotal);
    }

    #[Route('/category/{category_id:\d+}/posts', name: 'api.posts.index.for.category', methods: ['GET'])]
    public function indexForCategory(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $category_id = (int)$request->getAttribute('category_id');
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $countTotal = $repo->count(['category' => $category_id]);
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? $countTotal;
        $posts = $repo->findAllForCategory($category_id)
            ->setFirstResult((int)$offset)
            ->setMaxResults((int)$limit)
            ->getQuery()
            ->getResult();
        if (!$posts) {
            return new JsonResponse(400, 'Request out of range');
        }
        return $this->getResponseForList($posts, $request, (int)$offset, (int)$limit, $countTotal);
    }

    #[Route('/posts/{id:\d+}', name: 'api.post.show', methods: ['GET'])]
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $post = $repo->findAllForApi()
            ->andWhere("p.id = " . $id)
            ->getQuery()
            ->getResult();
        $response = new JsonResponse(200);
        if ($post) {
            $json = $this->serializer->serialize($post, 'json', ['groups' => ['group1', 'group3']]);
            /** @var $response ResponseInterface */
            return $response->withBody(Utils::streamFor($json));
        }
        return $response->withStatus(404)->withBody(Utils::streamFor("error: user with id $id not found"));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws FailedAccessException
     */
    #[Route('/posts', name: 'api.post.create', methods: ['POST'], middlewares: [BodyParserMiddleware::class])]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN', $request)) {
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour exécuter cette action');
        }
        $post = new Post();

        /** @var PostRepository $repo */
        $em = $this->om->getManager();
        $params = $this->getParams($request);
        $validator = $this->getValidator($request);
        if ($validator->isValid()) {
            Hydrator::hydrate($this->getParams($request, $post), $post);
            $post->setCreatedAt(new DateTimeImmutable());
            $em->persist($post);
            $em->flush();
            $json = $this->serializer->serialize($post, 'json', ['groups' => ['group1', 'group3']]);
            return new JsonResponse(201, $json);
        }
        Hydrator::hydrate($params, $post);
        $json = $this->serializer->serialize($post, 'json', ['groups' => ['group1', 'group3']]);
        $errors = $validator->getErrors();
        return new JsonResponse(400, json_encode($errors) . "\n" . $json);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NoRecordException
     * @throws FailedAccessException
     */
    #[Route('/posts/{id:\d+}', name: 'api.post.edit', methods: ['PATCH'], middlewares: [BodyParserMiddleware::class])]
    public function edit(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->authChecker->isGranted('ROLE_USER', $request)) {
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour exécuter cette action');
        }
        $id = $request->getAttribute('id');
        $repo = $this->em->getRepository(Post::class);
        $post = $repo->find($id);
        if(!$post) {
            throw new NoRecordException("error: user with id $id not found");
        }

        $validator = $this->getValidator($request);
        if ($validator->isValid()) {
            Hydrator::hydrate($this->getParams($request, $post), $post);
            $this->em->persist($post);
            $this->em->flush();
            $json = $this->serializer->serialize($post, 'json', ['groups' => ['group1', 'group3']]);
            return new JsonResponse(200, $json);
        }
        Hydrator::hydrate($this->getParams($request), $post);
        $json = $this->serializer->serialize($post, 'json', ['groups' => ['group1', 'group3']]);
        $errors = $validator->getErrors();
        return new JsonResponse(400, json_encode($errors) . "\n" . $json . json_encode($this->getParams($request)));
    }

    /**
     * @throws FailedAccessException
     */
    #[Route('/posts/{id:\d+}', name: 'api.post.delete', methods: ['DELETE'], middlewares: [BodyParserMiddleware::class])]
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->authChecker->isGranted('ROLE_ADMIN', $request)) {
            throw new FailedAccessException('Vous n\'avez pas l\'authorisation pour exécuter cette action');
        }
        /** @var Post $post */
        $post = $this->em->find(Post::class, $request->getAttribute('id'));
        $this->postUpload->delete($post->getImage());
        $this->em->remove($post);
        $this->em->flush();
        return new JsonResponse(200);
    }

    protected function getParams(ServerRequestInterface $request, ?Post $post = null): array
    {
        $params = array_merge($request->getParsedBody(), $request->getUploadedFiles());
        if ($post) {
            if (isset($params['delete']) && $params['delete'] == 1) {
                $this->postUpload->delete($post->getImage());
                $params['image'] = "";
            } elseif (isset($params['image'])) {
                // Upload du fichier
                $image = $this->postUpload->upload($params['image'], $post->getImage());
                if ($image) {
                    $params['image'] = $image;
                } else {
                    unset($params['image']);
                }
            }
        }

        $params = array_filter($params, function ($key) {
            return in_array($key, ['name', 'slug', 'content', 'created_at', 'category_id', 'image', 'published']);
        }, ARRAY_FILTER_USE_KEY);

        if ($post && $params['category_id'] !== ($post->getCategory()?->getId())) {
            $category = $this->om
                ->getManagerForClass(Category::class)
                ->find(Category::class, $params['category_id']);
            $params['category'] = $category;
            unset($params['category_id']);
        }
        return array_merge($params, [
            'updated_at' => new DateTime('now')
        ]);
    }

    private function getResponseForList(
        $posts,
        ServerRequestInterface $request,
        int $offset,
        int $limit,
        int $countTotal
    ): ResponseInterface {
        $count = count($posts);

        $json = $this->serializer->serialize($posts, 'json', ['groups' => ['group1', 'group3']]);
        $response = new JsonResponse(200, $json);
        if ($count < $countTotal) {
            $path = RequestUtils::getDomain($request) . $request->getUri()->getPath();
            $linkData = $this->getLinkData($countTotal, $offset, $limit);
            $first = (!empty($linkData['first'])) ?
                "$path?offset=" . $linkData['first']['offset'] . "&limit=" . $linkData['first']['limit'] . "; rel=\"first\", " :
                '';
            $prev = (!empty($linkData['prev'])) ?
                "$path?offset=" . $linkData['prev']['offset'] . "&limit=" . $linkData['prev']['limit'] . "; rel=\"prev\", " :
                '';
            $next = (!empty($linkData['next'])) ?
                "$path?offset=" . $linkData['next']['offset'] . "&limit=" . $linkData['next']['limit'] . "; rel=\"next\", " :
                '';
            $last = (!empty($linkData['last'])) ?
                "$path?offset=" . $linkData['last']['offset'] . "&limit=" . $linkData['last']['limit'] . "; rel=\"last\"" :
                '';
            $response = $response
                ->withStatus(206)
                ->withAddedHeader('Link', $first . $prev . $next . $last);
        }
        $limitRange = (($range = $offset + $limit) > $countTotal) ? $countTotal : $range;
        return $response
            ->withAddedHeader('Accept-Range', "posts $countTotal")
            ->withAddedHeader('Content-Range', $offset + 1 . "-" . $limitRange . "/$countTotal");
    }

    private function getLinkData(int $countTotal, int $offset, int $limit): array
    {
        $first = [];
        if ($offset > 0) {
            $firstOffset = 0;
            $firstLimit = min($limit, $offset);
            $first['offset'] = $firstOffset;
            $first['limit'] = $firstLimit;
        }

        $prev = [];
        $prevOffset = $offset - $limit;
        $prevLimit = $limit;
        if ($prevOffset > 0) {
            $prev['offset'] = $prevOffset;
            $prev['limit'] = $prevLimit;
        }

        $next = [];
        $nextOffset = $offset + $limit;
        $nextLimit = $limit;
        if ($nextOffset + $nextLimit < $countTotal) {
            $next['offset'] = $nextOffset;
            $next['limit'] = $nextLimit;
        }

        $last = [];
        $lastOffset = $countTotal - $limit;
        $lastLimit = $limit;
        if ($offset + $limit < $countTotal) {
            if ($lastOffset < $offset + $limit) {
                $lastOffset = $offset + $limit;
            }
            $lastOffset = max($lastOffset, ($next['offset'] ?? 0) + ($next['limit'] ?? 0));
            if ($lastOffset + $lastLimit > $countTotal) {
                $lastLimit = $countTotal - $lastOffset;
            }
            $last['offset'] = $lastOffset;
            $last['limit'] = $lastLimit;
        }

        return [
            'first' => $first,
            'prev' => $prev,
            'next' => $next,
            'last' => $last,
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @return Validator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return (new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles())))
            ->required('name', 'slug', 'content', 'category_id', 'published')
            ->addRules([
                'content' => 'min:2',
                'name' => 'range:2,250',
                'slug' => 'slug|range:2,100',
                'image' => 'filetype:[jpg,png]',
                'category_id' => 'exists:' . Category::class
            ]);
        //if (is_null($request->getAttribute('id'))) {
        //    $validator->uploaded('image');
        //}
        //return $validator;
    }
}