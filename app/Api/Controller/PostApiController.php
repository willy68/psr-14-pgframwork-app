<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Response\JsonResponse;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/v1')]
class PostApiController
{
    public function __construct(private ManagerRegistry $om)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/posts', name: 'api-posts-getList', methods: ['GET'])]
    public function getPosts(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 100;
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $posts = $repo->findAllForApi()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        $countTotal = $repo->count([]);
        $count = count($posts);

        $encoders = [new JsonEncoder()];

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ];

        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($posts, 'json', ['groups' => ['group1', 'group3']]);
        $response = new JsonResponse(200, $json);
        if ($count < $countTotal) {
            $domain = RequestUtils::getDomain($request);
            $first = "<$domain/api/v1/posts?offset=0&limit=$count>; rel=\"first\", ";
            $prev = ($offset - $count <= 0) ?
                '' :
                "<$domain/api/v1/posts?offset=" . $offset - $count . "&limit=$limit>; rel=\"prev\", ";
            $next = ($offset + $count) >= $countTotal ?
                '' :
                "<$domain/api/v1/posts?offset=" . $offset + $count . "&limit=$limit>; rel=\"next\", ";
            $last = "<$domain/api/v1/posts?offset=" . $countTotal - $limit . "&limit=$limit>; rel=\"last\"";
            $response = $response
                ->withStatus(206)
                ->withAddedHeader('Link',$first . $prev . $next . $last);
        }
        return $response
            ->withAddedHeader('Accept-Range', "posts 100")
            ->withAddedHeader('Content-Range', $offset + 1 . "-" . $offset + $limit . "/$countTotal");
    }

    #[Route('/category/{category_id:\d+}/posts', name: 'api-posts-getList-forCategory', methods: ['GET'])]
    public function getPostsForCategory(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 100;
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $posts = $repo->findAllForCategory($request->getAttribute('category_id'))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        return $this->getResponseForList($repo, $posts, $request, $offset, $limit);
    }

    //#[Route('/posts/{id:\d+}', name: 'api-post-getPost', methods: ['GET'])]
    public function getPost(ServerRequestInterface $request)
    {
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $post = $repo->buildFindAll()
            ->andWhere("p.id = " . $request->getAttribute('id'))
            ->getQuery()
            ->getResult();
    }

    private function getResponseForList(
        ObjectRepository $repository,
        $posts,
        ServerRequestInterface $request,
        int $offset,
        int $limit
    ): ResponseInterface {
        $countTotal = $repository->count([]);
        $count = count($posts);

        $encoders = [new JsonEncoder()];

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ];

        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($posts, 'json', ['groups' => ['group1', 'group3']]);
        $response = new JsonResponse(200, $json);
        if ($count < $countTotal) {
            $domain = RequestUtils::getDomain($request);
            $first = "<$domain/api/v1/posts?offset=0&limit=$count>; rel=\"first\", ";
            $prev = ($offset - $count <= 0) ?
                '' :
                "<$domain/api/v1/posts?offset=" . $offset - $count . "&limit=$limit>; rel=\"prev\", ";
            $next = ($offset + $count) >= $countTotal ?
                '' :
                "<$domain/api/v1/posts?offset=" . $offset + $count . "&limit=$limit>; rel=\"next\", ";
            $last = "<$domain/api/v1/posts?offset=" . $countTotal - $limit . "&limit=$limit>; rel=\"last\"";
            $response = $response
                ->withStatus(206)
                ->withAddedHeader('Link',$first . $prev . $next . $last);
        }
        return $response
            ->withAddedHeader('Accept-Range', "posts 100")
            ->withAddedHeader('Content-Range', $offset + 1 . "-" . $offset + $limit . "/$countTotal");
    }

}