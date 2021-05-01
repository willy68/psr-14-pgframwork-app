<?php

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
        $builder = new PaginatedQueryBuilder($this->_em);

        if (null !== $alias) {
            $builder
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy);
        }

        return $builder;
    }
}