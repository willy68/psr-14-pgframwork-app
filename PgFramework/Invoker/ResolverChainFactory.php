<?php

declare(strict_types=1);

namespace PgFramework\Invoker;

use Faker\Container\ContainerException;
use PgFramework\App;
use DI\Proxy\ProxyFactory;
use PgFramework\ApplicationInterface;
use PgFramework\Invoker\ParameterResolver\AssociativeArrayTypeHintResolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use DI\Invoker\DefinitionParameterResolver;
use PgFramework\Annotation\AnnotationsLoader;
use DI\Definition\Resolver\ResolverDispatcher;
use Doctrine\Persistence\ManagerRegistry;
use Invoker\ParameterResolver\ParameterResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineEntityResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use PgFramework\Invoker\ParameterResolver\DoctrineParamConverterAnnotations;
use Psr\Container\NotFoundExceptionInterface;

class ResolverChainFactory
{
	/**
	 * @param ContainerInterface $container
	 * @return ParameterResolver
	 */
	public function __invoke(ContainerInterface $container): ParameterResolver
	{
		$proxyDir = $this->getProxyDirectory($container);
		$definitionResolver = new ResolverDispatcher($container, new ProxyFactory($proxyDir));

		// Résolveurs par défaut
		$defaultResolvers = $this->getDefaultResolvers($container, $definitionResolver);

		// Résolveurs Doctrine
		$doctrineResolvers = $this->getDoctrineResolvers($container);

		return new ControllerParamsResolver(array_merge($doctrineResolvers, $defaultResolvers));
	}

	/**
	 * @param ContainerInterface $container
	 * @return string|null
	 */
	private function getProxyDirectory(ContainerInterface $container): ?string
	{
		try {
			if ($container->get('env') !== 'prod') {
				return null;
			}
			$projectDir = realpath($container->get(ApplicationInterface::class)->getProjectDir()) ?:
				$container->get(ApplicationInterface::class)->getProjectDir();
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			throw new ContainerException($e->getMessage(), $e->getCode(), $e);
		}
		return $projectDir . App::PROXY_DIRECTORY;
	}

	/**
	 * @param ContainerInterface $container
	 * @param ResolverDispatcher $definitionResolver
	 * @return array
	 */
	private function getDefaultResolvers(ContainerInterface $container, ResolverDispatcher $definitionResolver): array
	{
		return [
			new DefinitionParameterResolver($definitionResolver),
			new NumericArrayResolver(),
			new AssociativeArrayTypeHintResolver(),
			new DefaultValueResolver(),
			new TypeHintContainerResolver($container),
		];
	}

	/**
	 * @param ContainerInterface $container
	 * @return array
	 */
	private function getDoctrineResolvers(ContainerInterface $container): array
	{
		if (!$container->has(ManagerRegistry::class)) {
			return [];
		}

		try {
			$managerRegistry = $container->get(ManagerRegistry::class);
			return [
				new DoctrineParamConverterAnnotations($managerRegistry, $container->get(AnnotationsLoader::class)),
				new DoctrineEntityResolver($managerRegistry),
			];
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			throw new ContainerException($e->getMessage(), $e->getCode(), $e);
		}
	}
}


