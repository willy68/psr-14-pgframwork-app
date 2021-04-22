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

                $listeners = $defaultListeners;
                $mainListeners = $defaultMainListeners;

                if (isset($rule['no.default.listeners']) && $rule['no.default.listeners'] === true) {
                    $listeners = [];
                    $mainListeners = [];
                }

                $listeners = isset($rule['listeners']) ? array_merge($listeners, $rule['listeners']) : $listeners;
                $mainListeners = isset($rule['main.listeners']) ? array_merge($mainListeners, $rule['main.listeners']) : $mainListeners;

                $map->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['method'] ?? null,
                        $rule['host'] ?? null,
                        $rule['schemes'] ?? null,
                        $rule['port'] ?? null
                    ),
                    $listeners,
                    $mainListeners
                );
            }
        }
        return $map;
    }
}
