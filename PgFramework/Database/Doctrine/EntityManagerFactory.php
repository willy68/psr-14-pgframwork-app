<?php

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class EntityManagerFactory
{
    public function __invoke(ContainerInterface $c, string $connectionEntry): EntityManager
    {
        return EntityManager::create($c->get($connectionEntry), $c->get(Configuration::class));
    }
}
