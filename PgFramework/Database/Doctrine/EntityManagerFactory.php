<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use DI\NotFoundException;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
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
		if (!$c->has(Configuration::class)) {
			throw new NotFoundException('EntityManager config not found in DI container');
		}
        return new EntityManager($c->get($connectionEntry), $c->get(Configuration::class));
    }
}
