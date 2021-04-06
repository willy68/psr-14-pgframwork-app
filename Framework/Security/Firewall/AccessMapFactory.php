<?php

namespace Framework\Security\Firewall;

use Framework\Router\RequestMatcher;
use Psr\Container\ContainerInterface;

class AccessMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $accessMap = new AccessMap();
        if ($c->has('security.voters.rules')) {
            $rules = $c->get('security.voters.rules');

            foreach ($rules as $rule) {
                $accessMap->add(
                    new RequestMatcher(
                        $rule['path'] ?? null,
                        $rule['method'] ?? null,
                        $rule['host'] ?? null,
                        $rule['schemes'] ?? null,
                        $rule['port'] ?? null
                    ),
                    $rule['attributes'] ?? [],
                    $rule['main.listeners'] ?? []
                );
            }
        }
        return $accessMap;
    }
}
