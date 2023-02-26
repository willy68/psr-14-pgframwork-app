<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EntityManagerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws MissingMappingDriverImplementation
     */
    public function __invoke(ContainerInterface $c, string $connectionEntry): EntityManagerInterface
    {
        $debugStack = new DebugStack();
        $entityManager = new EntityManager($c->get($connectionEntry), $c->get(Configuration::class));
        $entityManager->getConnection()->getConfiguration()->setSQLLogger($debugStack);
        return $entityManager;
    }
}
