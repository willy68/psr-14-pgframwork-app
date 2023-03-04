<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AttributeReader;
use Doctrine\Persistence\ManagerRegistry;
use PgFramework\Response\JsonResponse;
use PgFramework\Router\Annotation\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
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
        $limit = $params['limit'] ?? 12;
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $posts = $repo->findAllForApi()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $encoders = [new JsonEncoder()];

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ];

        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($posts, 'json', ['groups' => ['group1', 'group3']]);
        return (new JsonResponse(200, $json));
    }

    public function getPostsForCategory(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $posts = $repo->buildFindPublicForCategory();

    }

    //#[Route('/categories/{category_id:\d+}/posts/{id:\d+}', name: 'api-post-getPost', methods: ['GET'])]
    public function getPost(ServerRequestInterface $request)
    {
        /** @var PostRepository $repo */
        $repo = $this->om->getManager()->getRepository(Post::class);
        $post = $repo->buildFindAll()
            ->andWhere("p.id = " . $request->getAttribute('id'))
            ->getQuery()
            ->getResult();
    }

}