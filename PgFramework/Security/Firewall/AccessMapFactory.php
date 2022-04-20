<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall;

use PgFramework\Router\RequestMatcher;
use Psr\Container\ContainerInterface;

class AccessMapFactory
{
    public function __invoke(ContainerInterface $c)
    {
        $map = new AccessMap();
        if ($c->has('security.firewall.rules')) {
            $rules = $c->get('security.firewall.rules');

            foreach ($rules as $rule) {
                if (isset($rule['voters.rules'])) {
                    foreach ($rule['voters.rules'] as $voter) {
                        $map->add(
                            new RequestMatcher(
                                isset($rule['path']) ?
                                    (isset($voter['path']) ? $voter['path'] : $rule['path']) :
                                    $voter['path'] ?? null,
                                isset($rule['method']) ?
                                    (isset($voter['method']) ? $voter['method'] : $rule['method']) :
                                    $voter['method'] ?? null,
                                isset($rule['host']) ?
                                    (isset($voter['host']) ? $voter['host'] : $rule['host']) :
                                    $voter['host'] ?? null,
                                isset($rule['schemes']) ?
                                    (isset($voter['schemes']) ? $voter['schemes'] : $rule['schemes']) :
                                    $voter['schemes'] ?? null,
                                isset($rule['port']) ?
                                    (isset($voter['port']) ? $voter['port'] : $rule['port']) :
                                    $voter['port'] ?? null
                            ),
                            $voter['attributes'] ?? []
                        );
                    }
                }
            }
        }
        return $map;
    }
}
