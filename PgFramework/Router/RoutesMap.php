<?php

declare(strict_types=1);

namespace PgFramework\Router;

use Psr\Http\Message\ServerRequestInterface;

class RoutesMap implements RoutesMapInterface
{
    private array $map = [];

    public function add(RequestMatcherInterface $requestMatcher = null, array $listeners = []): void
    {
        $this->map[] = [$requestMatcher, $listeners];
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners(ServerRequestInterface $request): array
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->match($request)) {
                return [$elements[1]];
            }
        }

        return [[]];
    }
}
