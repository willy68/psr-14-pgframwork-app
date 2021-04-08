<?php

namespace Framework\Security\Firewall;

use Framework\Router\RequestMatcher;
use Psr\Container\ContainerInterface;

class AccessMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $map = new AccessMap;

        if ($c->has('security.voters.rules')) {
            $rules = $c->get('security.voters.rules');

            foreach ($rules as $rule) {
                $map->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['method'] ?? null,
                        $rule['host'] ?? null,
                        $rule['schemes'] ?? null,
                        $rule['port'] ?? null
                    ),
                    $rule['attributes'] ?? []
                );
            }
        }
        return $map;
    }
}
