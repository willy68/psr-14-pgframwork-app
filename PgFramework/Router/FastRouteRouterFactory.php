<?php

/**
 * @see       https://github.com/mezzio/mezzio-fastroute for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-fastroute/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-fastroute/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PgFramework\Router;

use Mezzio\Router\FastRouteRouter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Create and return an instance of FastRouteRouter.
 *
 * Configuration should look like the following:
 *
 * <code>
 * 'router' => [
 *     'fastroute' => [
 *         'cache_enabled' => true, // true|false
 *         'cache_file'   => '(/absolute/)path/to/cache/file', // optional
 *     ],
 * ]
 * </code>
 */
class FastRouteRouterFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FastRouteRouter
    {
        $cache = null;
        if ($container->get('env') === 'prod') {
            $cache = $container->get('app.cache.dir') . '/route';
        }

        return new FastRouteRouter(null, null, [
            FastRouteRouter::CONFIG_CACHE_ENABLED => !is_null($cache),
            FastRouteRouter::CONFIG_CACHE_FILE => $cache
        ]);
    }
}
