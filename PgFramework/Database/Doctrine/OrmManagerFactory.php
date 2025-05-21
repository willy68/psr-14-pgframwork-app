<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use DI\NotFoundException;
use PgFramework\Database\Doctrine\Bridge\DebugStack;
use PgFramework\DebugBar\DataCollector\DoctrineCollector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\NotFoundExceptionInterface;

class OrmManagerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws DebugBarException
     */
    public function __invoke(ContainerInterface $c): ManagerRegistry
    {
		if (!$c->has('doctrine.connections') || !$c->has('doctrine.managers')) {
			throw new NotFoundException('Doctrine connections and managers entries are required.');
		}

        $debug = $c->get('env') !== 'prod';
        $om = new OrmManagerRegistry(
            $c->get('doctrine.connections'),
            $c->get('doctrine.managers'),
            'default',
            'default',
            $c
        );

        if ($debug && $c->has(DebugBar::class)) {
            /** @var DebugBar $debugBar*/
            $debugBar = $c->get(DebugBar::class);
            $debugBar->addCollector(new DoctrineCollector($c->get(DebugStack::class)));
        }

        return $om;
    }
}
