<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
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
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $countTotal = $repo->count([]);
        $offset = (int)$params['offset'] ?? 0;
        $limit = (int)$params['limit'] ?? $countTotal;
        $posts = $repo->findAllForApi()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        if (!$posts) {
            return new JsonResponse(400, 'Request out of range');
        }
        return $this->getResponseForList($posts, $request, $offset, $limit, $countTotal);
    }

    #[Route('/category/{category_id:\d+}/posts', name: 'api-posts-getList-forCategory', methods: ['GET'])]
    public function getPostsForCategory(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $category_id = (int)$request->getAttribute('category_id');
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $countTotal = $repo->count(['category' => $category_id]);
        $offset = (int)$params['offset'] ?? 0;
        $limit = (int)$params['limit'] ?? $countTotal;
        $posts = $repo->findAllForCategory($category_id)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        if (!$posts) {
            return new JsonResponse(400, 'Request out of range');
        }
        return $this->getResponseForList($posts, $request, $offset, $limit, $countTotal);
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
                               $posts,
        ServerRequestInterface $request,
        int                    $offset,
        int                    $limit,
        int                    $countTotal
    ): ResponseInterface
    {
        $count = count($posts);

        $json = $this->serializer->serialize($posts, 'json', ['groups' => ['group1', 'group3']]);
        $response = new JsonResponse(200, $json);
        if ($count < $countTotal) {
            $path = RequestUtils::getDomain($request) . $request->getUri()->getPath();
            $linkData = $this->getLinkData($countTotal, $offset, $limit);
            $first = (!empty($linkData['first'])) ?
                "<$path?offset=" . $linkData['first']['offset'] ."&limit=" . $linkData['first']['limit'] . ">; rel=\"first\", " :
                '';
            $prev = (!empty($linkData['prev'])) ?
                "<$path?offset=" . $linkData['prev']['offset'] . "&limit=" . $linkData['prev']['limit']  .">; rel=\"prev\", " :
                '';
            $next = (!empty($linkData['next'])) ?
                "<$path?offset=" . $linkData['next']['offset'] . "&limit=" . $linkData['next']['limit']  .">; rel=\"next\", " :
                '';
            $last = (!empty($linkData['last'])) ?
                "<$path?offset=" . $linkData['last']['offset'] . "&limit=" . $linkData['next']['limit'] . ">; rel=\"last\"" :
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
        $firstOffset = 0;
        $firstLimit = $limit;
        if ($firstOffset + $limit >= $offset) {
            $firstLimit = $offset;
        }
        if ($offset > 0) {
            $first['offset'] = $firstOffset;
            $first['limit'] = $firstLimit;
        }

        $prev = [];
        $prevOffset = $offset - $limit;
        $prevLimit = $limit;
        if ($prevOffset < 0) {
            $prevOffset = 0;
            $prevLimit = $offset;
        }
        if ($offset > 0) {
            $prev['offset'] = $prevOffset;
            $prev['limit'] = $prevLimit;
        }

        $next = [];
        $nextOffset = $offset + $limit;
        $nextLimit = $limit;
        if ($nextOffset < $countTotal) {
            if ($nextOffset + $nextLimit > $countTotal) {
                $nextLimit =  $countTotal - $nextOffset;
            }
            $next['offset'] = $nextOffset;
            $next['limit'] = $nextLimit;
        }

        $last = [];
        $lastOffset = $countTotal - $limit;
        $lastLimit = $limit;
        if ($lastOffset + $lastLimit > $countTotal) {
            $lastLimit = $countTotal - $lastOffset + $lastLimit;
        }
        if ($lastOffset < $offset + $limit) {
            $lastOffset = $offset + $limit;
        }
        if ($offset + $limit < $countTotal) {
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

}