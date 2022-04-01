<?php

namespace PgFramework\Database\Doctrine;

use DebugBar\DebugBar;
use Doctrine\DBAL\Logging\DebugStack;
use Psr\Container\ContainerInterface;
use DebugBar\Bridge\DoctrineCollector;
use Doctrine\Persistence\ManagerRegistry;

class OrmManagerFactory
{
    public function __invoke(ContainerInterface $c): ManagerRegistry
    {
        $debug = $c->get('env') !== 'prod';
        $om = new OrmManagerRegistry(
            $c->get('doctrine.connections'),
            $c->get('doctrine.managers'),
            'default',
            'default',
            $c
        );

        if ($debug && $c->has(DebugBar::class)) {
            /** @var DebugBar */
            $debugBar = $c->get(DebugBar::class);
            $debugStack = new DebugStack();
            $om->getConnection()->getConfiguration()->setSQLLogger($debugStack);
            $debugBar->addCollector(new DoctrineCollector($debugStack));
        }

        return $om;
    }
}
