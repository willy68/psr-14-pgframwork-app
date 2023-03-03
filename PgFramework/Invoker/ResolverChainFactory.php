<?php

declare(strict_types=1);

namespace PgFramework\Invoker;

use PgFramework\App;
use DI\Proxy\ProxyFactory;
use PgFramework\ApplicationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use DI\Invoker\DefinitionParameterResolver;
use PgFramework\Annotation\AnnotationsLoader;
use DI\Definition\Resolver\ResolverDispatcher;
use Doctrine\Persistence\ManagerRegistry;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineEntityResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineParamConverterAnnotations;
use Psr\Container\NotFoundExceptionInterface;

class ResolverChainFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ParameterResolver
    {
        $proxyDir = null;
        if ($container->get('env') === 'prod') {
            $projectDir = $container->get(ApplicationInterface::class)->getProjectDir();
            $projectDir = realpath($projectDir) ?: $projectDir;
            $proxyDir = $projectDir . App::PROXY_DIRECTORY;
        }

        $definitionResolver = new ResolverDispatcher($container, new ProxyFactory($proxyDir));

        return new ControllerParamsResolver([
            new DoctrineParamConverterAnnotations(
                $container->get(ManagerRegistry::class),
                $container->get(AnnotationsLoader::class)
            ),
            new DoctrineEntityResolver($container->get(ManagerRegistry::class)),
            new DefinitionParameterResolver($definitionResolver),
            new NumericArrayResolver(),
            new AssociativeArrayResolver(),
            new DefaultValueResolver(),
            new TypeHintContainerResolver($container)
        ]);
    }
}
