<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PgFramework\Security\Firewall;

use PgFramework\Router\RequestMatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * FirewallMap allows configuration of different firewalls for specific parts
 * of the website.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author William Lety <william.lety@gmail.com>
 */
class FirewallMap implements FirewallMapInterface
{
    private $map = [];

    public function add(RequestMatcherInterface $requestMatcher = null, array $listeners = [], $mainListener = [])
    {
        $this->map[] = [$requestMatcher, $listeners, $mainListener];
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners(ServerRequestInterface $request)
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->match($request)) {
                return [$elements[1], $elements[2]];
            }
        }

        return [[], []];
    }
}