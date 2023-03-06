<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Psr7\Utils;
use PgFramework\HttpUtils\RequestUtils;
use PgFramework\Response\JsonResponse;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1')]
class PostApiController
{
    public function __construct(private ManagerRegistry $om, private SerializerInterface $serializer)
    {
    }

    #[Route('/posts', name: 'api-posts-getList', methods: ['GET'])]
    public function getPosts(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $offset = (int)$params['offset'] ?? 0;
        $limit = (int)$params['limit'] ?? 100;
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $countTotal = $repo->count([]);
        $posts = $repo->findAllForApi()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        return $this->getResponseForList($repo, $posts, $request, $offset, $limit, $countTotal);
    }

    #[Route('/category/{category_id:\d+}/posts', name: 'api-posts-getList-forCategory', methods: ['GET'])]
    public function getPostsForCategory(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $offset = (int)$params['offset'] ?? 0;
        $limit = (int)$params['limit'] ?? 100;
        $category_id = (int)$request->getAttribute('category_id');
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $countTotal = $repo->count(['category' => $category_id]);
        $posts = $repo->findAllForCategory($category_id)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        return $this->getResponseForList($repo, $posts, $request, $offset, $limit, $countTotal);
    }

    #[Route('/posts/{id:\d+}', name: 'api-post-getPost', methods: ['GET'])]
    public function getPost(ServerRequestInterface $request): ResponseInterface
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
            return $response->withBody(Utils::streamFor($json));
        }
        return $response->withStatus(404)->withBody(Utils::streamFor("error: user with id $id not found"));
    }

    private function getResponseForList(
        ObjectRepository $repository,
        $posts,
        ServerRequestInterface $request,
        int $offset,
        int $limit,
        $countTotal
    ): ResponseInterface {
        $count = count($posts);

        $json = $this->serializer->serialize($posts, 'json', ['groups' => ['group1', 'group3']]);
        $response = new JsonResponse(200, $json);
        if ($count < $countTotal) {
            $path = RequestUtils::getDomain($request) . $request->getUri()->getPath();
            $first = "<$path?offset=0&limit=" . min($offset, $countTotal, $limit) . ">; rel=\"first\", ";
            $prev = ($offset - $count <= 0) ?
                '' :
                "<$path?offset=" . $offset - $count . "&limit=$limit>; rel=\"prev\", ";
            $next = ($offset + $count) >= $countTotal ?
                '' :
                "<$path?offset=" . $offset + $count . "&limit=" . min($limit, $countTotal - ($offset + $count)). ">; rel=\"next\", ";
            $last = "<$path?offset=" . $countTotal - $limit . "&limit=$limit>; rel=\"last\"";
            $response = $response
                ->withStatus(206)
                ->withAddedHeader('Link',$first . $prev . $next . $last);
        }
        $limitRange = ($offset + $limit > $countTotal) ? $countTotal : $offset + $limit;
        return $response
            ->withAddedHeader('Accept-Range', "posts $countTotal")
            ->withAddedHeader('Content-Range', $offset + 1 . "-" . $limitRange . "/$countTotal");
    }

}