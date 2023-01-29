<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall;

use Psr\Container\ContainerInterface;
use PgFramework\Router\RequestMatcher;

class FirewallMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $map = new FirewallMap();

        if ($c->has('security.firewall.rules')) {
            $rules = $c->get('security.firewall.rules');
            $listeners = [];
            $mainListeners = [];

            foreach ($rules as $rule) {
                if (isset($rule['default.listeners']) || isset($rule['default.main.listeners'])) {
                    $listeners = $rule['default.listeners'] ?? [];
                    $mainListeners = $rule['default.main.listeners'] ?? [];
                    continue;
                }

                if (isset($rule['no.default.listeners']) && $rule['no.default.listeners'] === true) {
                    $listeners = [];
                    $mainListeners = [];
                }

                $listeners = isset($rule['listeners']) ? array_merge($listeners, $rule['listeners']) : $listeners;
                $mainListeners = isset($rule['main.listeners']) ?
                    array_merge($mainListeners, $rule['main.listeners']) :
                    $mainListeners;

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
