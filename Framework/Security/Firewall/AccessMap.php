<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Framework\Security\Firewall;

use Psr\Http\Message\ServerRequestInterface;
use Framework\Router\RequestMatcherInterface;

/**
 * AccessMap allows configuration of different access control rules for
 * specific parts of the website.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AccessMap implements AccessMapInterface
{
    private $map = [];

    /**
     * @param array     $attributes An array of attributes to pass to the access decision manager (like roles)
     * @param array     $listeners  Main listeners (ResponseEvent or ExceptionEvent)
     */
    public function add(RequestMatcherInterface $requestMatcher, array $attributes = [], $listeners = [])
    {
        $this->map[] = [$requestMatcher, $attributes, $listeners];
    }

    /**
     * {@inheritdoc}
     */
    public function getPatterns(ServerRequestInterface $request)
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->match($request)) {
                return [$elements[1], $elements[2]];
            }
        }

        return [null, null];
    }
}