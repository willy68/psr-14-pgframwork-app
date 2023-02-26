<?php

declare(strict_types=1);

namespace PgFramework\Database\ActiveRecord;

use ActiveRecord;
use ActiveRecord\Connection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Invoker\ParameterResolver\ResolverChain;
use PgFramework\Annotation\AnnotationsLoader;
use Invoker\ParameterResolver\ParameterResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordResolver;
use PgFramework\Invoker\ParameterResolver\ActiveRecordAnnotationsResolver;
use Psr\Container\NotFoundExceptionInterface;

class ActiveRecordFactory
{
    /**
     * Initialize ActiveRecord Factory
     *
     * @param ContainerInterface $c
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): bool
    {
        ActiveRecord\Config::initialize(function ($cfg) use ($c) {
            $cfg->set_connections(
                $c->get('ActiveRecord.connections')
            );

            // default connection is now development
            $cfg->set_default_connection('development');

            // Datetime format
            Connection::$datetime_format = 'Y-m-d H:i:s';
        });

        /** @var  ResolverChain $paramResolver*/
        $paramResolver = $c->get(ParameterResolver::class);
        $paramResolver->prependResolver(new ActiveRecordAnnotationsResolver($c->get(AnnotationsLoader::class)));
        $paramResolver->prependResolver(new ActiveRecordResolver());
        return true;
    }
}
