<?php

declare(strict_types=1);

namespace PgFramework\Invoker;

use Invoker\CallableResolver;
use Psr\Container\ContainerInterface;

class CallableResolverFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new CallableResolver($container);
    }
}
