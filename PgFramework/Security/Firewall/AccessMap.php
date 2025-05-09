<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PgFramework\Security\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Router\RequestMatcherInterface;

/**
 * AccessMap allows configuration of different access control rules for
 * specific parts of the website.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AccessMap implements AccessMapInterface
{
    private array $map = [];

    /**
     * @param array     $attributes An array of attributes to pass to the access decision manager (like roles)
     */
    public function add(RequestMatcherInterface $requestMatcher, array $attributes = [])
    {
        $this->map[] = [$requestMatcher, $attributes];
    }

    public function getPatterns(ServerRequestInterface $request): array
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->match($request)) {
                return [$elements[1]];
            }
        }
        return [null];
    }
}
