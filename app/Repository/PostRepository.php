<?php

namespace App\Repository;

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
            ->from($this->getEntityName(), 'p')
            ->join('p.category', 'c', 'c = p.category')
            ->orderBy('p.createdAt', 'DESC');

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
            ->andWhere('p.createdAt < CURRENT_TIMESTAMP()');
    }
    /**
     * Get all records for one category
     *
     * @param int $category_id
     * @return PaginatedQueryBuilder
     */
    public function buildFindPublicForCategory(int $category_id): PaginatedQueryBuilder
    {
        return $this->buildFindPublic()->andWhere("p.category = $category_id");
    }

    /**
     * paginate Posts
     *
     * @param QueryBuilder $query
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
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
     * @return mixed
     */
    public function findWithCategory(int $id): mixed
    {
        $builder = $this->buildFindPublic()->andWhere("p.id = $id");
        $query = $builder->getQuery();
        return $query->getResult();
    }

    /**
     * Get all posts order by ID ASC for API
     * @return PaginatedQueryBuilder
     */
    public function findAllForApi(): PaginatedQueryBuilder
    {
        $builder = $this->createQueryBuilder();
        $builder->select('p')
            ->from($this->getEntityName(), 'p')
            ->leftJoin('p.category', 'c','c = p.category');
            //->orderBy('p.id,', 'ASC');
        return $builder;
    }
}
