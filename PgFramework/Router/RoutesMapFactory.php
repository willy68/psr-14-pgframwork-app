<?php

namespace PgFramework\Router;

use Psr\Container\ContainerInterface;

class RoutesMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $map = new RoutesMap();

        if ($c->has('routes.listeners')) {
            $rules = $c->get('routes.listeners');

            foreach ($rules as $rule) {
                $map->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['method'] ?? null,
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
