<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class PostRepository extends EntityRepository
{
    public function findPublic()
    {
        $builder = $this->buildFindPublic();
        $query = $this->getEntityManager()->createQuery($builder->getDQL());
        return $query->getResult();
    }

    public function buildFindAll(): QueryBuilder
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('p')
            ->from(Post::class, 'p')
            ->join('p.category', 'c', 'c = p.category')
            ->orderBy('p.created_at', 'DESC');

        return $builder;
    }

    public function buildFindPublic(): QueryBuilder
    {
        return $this->buildFindAll()
            ->where('p.published = 1')
            ->andWhere('p.created_at < CURRENT_TIMESTAMP()');
    }
    /**
     *
     * @param int $category_id
     * @return Query
     */
    public function buildFindPublicForCategory(int $category_id): QueryBuilder
    {
        return $this->buildFindPublic()->andWhere("p.category = $category_id");
    }

    /**
     *
     * @param int $id
     */
    public function findWithCategory(int $id)
    {
        $builder = $this->buildFindPublic()->andWhere("p.id = $id");
        $query = $this->getEntityManager()->createQuery($builder->getDQL());
        return $query->getResult();
    }
}