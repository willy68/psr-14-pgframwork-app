<?php

namespace PgFramework\Database\Doctrine;

use Doctrine\DBAL\Configuration;
use PgFramework\Database\Doctrine\Bridge\DebugMiddleware;
use PgFramework\Database\Doctrine\Bridge\DebugStack;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ConnectionConfigFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): Configuration
    {
        $config = new Configuration();
        if ($c->get('env') !== 'prod' && $c->has(DebugStack::class)) {
            /** @var DebugStack $debugStack */
            $debugStack = $c->get(DebugStack::class);
            $config->setMiddlewares([new DebugMiddleware($debugStack)]);
        }
        return $config;
    }
}
