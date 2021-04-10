<?php

namespace PgFramework\Security\Firewall;

use PgFramework\Router\RequestMatcher;
use Psr\Container\ContainerInterface;

class FirewallMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $map = new FirewallMap;

        if ($c->has('firewall.event.rules')) {
            $rules = $c->get('firewall.event.rules');

            foreach ($rules as $rule) {
                $map->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['method'] ?? null,
                        $rule['host'] ?? null,
                        $rule['schemes'] ?? null,
                        $rule['port'] ?? null
                    ),
                    $rule['listeners'] ?? [],
                    $rule['main.listeners'] ?? []
                );
            }
        }
        return $map;
    }
}
