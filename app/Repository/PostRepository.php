<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use PgFramework\Database\Doctrine\PaginatedQueryBuilder;
use PgFramework\Database\Doctrine\PaginatedEntityRepository;

class PostRepository extends PaginatedEntityRepository
{
    /**
     * Get all records with category
     *
     * @return PaginatedQueryBuilder
     */
    public function buildFindAll(): PaginatedQueryBuilder
    {
        $builder = $this->createQueryBuilder();
        $builder->select('p')
            ->from(Post::class, 'p')
            ->join('p.category', 'c', 'c = p.category')
            ->orderBy('p.created_at', 'DESC');

        return $builder;
    }

    /**
     * Get all public records with category
     *
     * @return PaginatedQueryBuilder
     */
    public function buildFindPublic(): PaginatedQueryBuilder
    {
        return $this->buildFindAll()
            ->where('p.published = 1')
            ->andWhere('p.created_at < CURRENT_TIMESTAMP()');
    }
    /**
     * Get all records for one category
     *
     * @param int $category_id
     * @return Query
     */
    public function buildFindPublicForCategory(int $category_id): PaginatedQueryBuilder
    {
        return $this->buildFindPublic()->andWhere("p.category = $category_id");
    }

    /**
     * paginate Posts
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param int $perPage
     * @param int $currentPage
     * @return \Pagerfanta\Pagerfanta
     */
    public function paginate(QueryBuilder $query, int $perPage, int $currentPage = 1): Pagerfanta
    {
        $paginator = new QueryAdapter($query);
        return (new Pagerfanta($paginator))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * Get one record for one category
     *
     * @param int $id
     */
    public function findWithCategory(int $id)
    {
        $builder = $this->buildFindPublic()->andWhere("p.id = $id");
        $query = $builder->getQuery();
        return $query->getResult();
    }
}
