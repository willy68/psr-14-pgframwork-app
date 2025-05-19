<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Tools\DsnParser;
use PgFramework\Database\Doctrine\Bridge\DebugMiddleware;
use PgFramework\Database\Doctrine\Bridge\DebugStack;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DbalConnectionFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function __invoke(ContainerInterface $c, string $url, string $connectionName)
    {
        $config = new Configuration();
        if ($c->get('env') !== 'prod') {
            /** @var DebugStack $debugStack */
            $debugStack = $c->get(DebugStack::class);
            $config->setMiddlewares([new DebugMiddleware($debugStack, $connectionName)]);
        }

		$dsnParser = new DsnParser();
		$connectionParams = $dsnParser->parse($c->get($url)[$connectionName]);
        return DriverManager::getConnection($connectionParams, $config);
    }
}
