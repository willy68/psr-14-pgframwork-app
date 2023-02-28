<?php

declare(strict_types=1);

namespace PgFramework\Router;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RoutesMapFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): RoutesMap
    {
        $map = new RoutesMap();

        if ($c->has('routes.listeners')) {
            $rules = $c->get('routes.listeners');

            foreach ($rules as $rule) {
                $map->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['methods'] ?? null,
                        $rule['host'] ?? null,
                        $rule['schemes'] ?? null,
                        $rule['port'] ?? null
                    ),
                    $rule['listeners'] ?? []
                );
            }
        }
        return $map;
    }
}
