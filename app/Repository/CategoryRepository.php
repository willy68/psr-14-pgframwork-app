<?php

namespace App\Repository;

use App\Entity\Category;
use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use PgFramework\Database\Doctrine\PaginatedQueryBuilder;
use PgFramework\Database\Doctrine\PaginatedEntityRepository;

class CategoryRepository extends PaginatedEntityRepository
{
    /**
     * Get all records with category
     *
     * @return PaginatedQueryBuilder
     */
    public function buildFindAll(): PaginatedQueryBuilder
    {
        $builder = $this->createQueryBuilder();
        $builder->select('c')
            ->from(Category::class, 'c');

        return $builder;
    }

    /**
     * paginate Category
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
}
