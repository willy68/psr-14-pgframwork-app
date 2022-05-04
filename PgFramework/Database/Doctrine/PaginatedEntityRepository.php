<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;

class PaginatedEntityRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    public function createQueryBuilder($alias = null, $indexBy = null)
    {
        $builder = new PaginatedQueryBuilder($this->getEntityManager());

        if (null !== $alias) {
            $builder
            ->select($alias)
            ->from($this->getEntityName(), $alias, $indexBy);
        }

        return $builder;
    }
}
