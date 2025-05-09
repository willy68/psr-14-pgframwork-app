<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall;

use PgFramework\Router\RequestMatcher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AccessMapFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): AccessMap
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
                                    ($voter['path'] ?? $rule['path']) :
                                    $voter['path'] ?? null,
                                isset($rule['method']) ?
                                    ($voter['method'] ?? $rule['method']) :
                                    $voter['method'] ?? null,
                                isset($rule['host']) ?
                                    ($voter['host'] ?? $rule['host']) :
                                    $voter['host'] ?? null,
                                isset($rule['schemes']) ?
                                    ($voter['schemes'] ?? $rule['schemes']) :
                                    $voter['schemes'] ?? null,
                                isset($rule['port']) ?
                                    ($voter['port'] ?? $rule['port']) :
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
