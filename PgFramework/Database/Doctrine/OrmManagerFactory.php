<?php

namespace PgFramework\Database\Doctrine;

use Psr\Container\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;

class OrmManagerFactory
{
    public function __invoke(ContainerInterface $c): ManagerRegistry
    {
        return new OrmManagerRegistry(
            $c->get('doctrine.connections'),
            $c->get('doctrine.managers'),
            'default',
            'default',
            $c
        );
    }
}
