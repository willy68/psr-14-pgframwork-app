<?php

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Logging\DebugStack;
use Psr\Container\ContainerInterface;

class EntityManagerFactory
{
    public function __invoke(ContainerInterface $c, string $connectionEntry): EntityManager
    {
        $debugStack = new DebugStack();
        $entityManager = EntityManager::create($c->get($connectionEntry), $c->get(Configuration::class));
        $entityManager->getConnection()->getConfiguration()->setSQLLogger($debugStack);
        return $entityManager;
    }
}
