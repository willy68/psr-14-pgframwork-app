<?php

namespace PgFramework\Security\Firewall;

use Psr\Container\ContainerInterface;
use PgFramework\Router\RequestMatcher;

class FirewallMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $map = new FirewallMap;

        if ($c->has('security.firewall.rules')) {
            $rules = $c->get('security.firewall.rules');
            $defaultListeners = [];
            $defaultMainListeners = [];
            foreach ($rules as $rule) {
                if (isset($rule['default.listeners']) || isset($rule['default.main.listeners'])) {
                    $defaultListeners = $rule['default.listeners'] ?? [];
                    $defaultMainListeners = $rule['default.main.listeners'] ?? [];
                    continue;
                }

                $defaultListeners = isset($rule['listeners']) ? array_merge($defaultListeners, $rule['listeners']) : $defaultListeners;
                $defaultMainListeners = isset($rule['main.listeners']) ? array_merge($defaultMainListeners, $rule['main.listeners']) : $defaultMainListeners;

                $map->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['method'] ?? null,
                        $rule['host'] ?? null,
                        $rule['schemes'] ?? null,
                        $rule['port'] ?? null
                    ),
                    $defaultListeners,
                    $defaultMainListeners
                );
            }
        }
        return $map;
    }
}
