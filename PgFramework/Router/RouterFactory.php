<?php

/** @see       https://github.com/willy68/pg-router for the canonical source repository */

declare(strict_types=1);

namespace PgFramework\Router;

use Pg\Router\Router;
use Psr\Cache\CacheException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Create and return an instance of PgRouter.
 *
 * Configuration should look like the following:
 *
 * <code>
 *  $router = new Router(
 *      null,
 *      null,
 *      [
 *           Router::CONFIG_CACHE_ENABLED => ($env === 'prod'),
 *           Router::CONFIG_CACHE_DIR => '/tmp/cache/router',
 *           Router::CONFIG_CACHE_POOL_FACTORY => function (): CacheItemPoolInterface {...},
 *      ]
 *  );
 * </code>
 */
class RouterFactory
{
    /**
     * @param ContainerInterface $container
     * @return Router
     * @throws CacheException
     */
    public function __invoke(ContainerInterface $container): Router
    {
        $cacheEnable = false;
        try {
            $cacheEnable = $container->get('env') === 'prod';
        } catch (ContainerExceptionInterface) {
        }

        $config = null;
        if ($cacheEnable && $container->has('app.cache.dir')) {
            try {
                $cacheDir = $container->get('app.cache.dir');
            } catch (ContainerExceptionInterface) {
                $cacheDir = null;
            }
            $config = [
                Router::CONFIG_CACHE_ENABLED => true,
                Router::CONFIG_CACHE_DIR => $cacheDir . '/Router',
                Router::CONFIG_CACHE_POOL_FACTORY => null,
            ];
        }

        return new Router(
            null,
            null,
            $config
        );
    }
}
