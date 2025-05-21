<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\Configuration;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Exception\CacheException;

class DoctrineConfigFactory
{
	/**
	 * @throws ContainerExceptionInterface
	 * @throws CacheException
	 */
    public function __invoke(ContainerInterface $c): Configuration
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode = $c->get('env') === 'dev';
        $config = new Configuration();

        if ($isDevMode === true) {
            $queryCache = new ArrayAdapter();
            $metadataCache = new ArrayAdapter();
            $hydrateCache = new ArrayAdapter();
            $resultCache = new ArrayAdapter();
            $config->setAutoGenerateProxyClasses(true);
        } else {
            $queryCache = new PhpFilesAdapter('doctrine_queries', 0, $c->get('app.cache.dir') . '/orm');
            $metadataCache = new PhpFilesAdapter('doctrine_metadata', 0, $c->get('app.cache.dir') . '/orm');
            $hydrateCache = new PhpFilesAdapter('doctrine.hydrate', 0, $c->get('app.cache.dir') . '/orm');
            $resultCache = new PhpFilesAdapter('doctrine.result', 0, $c->get('app.cache.dir') . '/orm');
            $config->setAutoGenerateProxyClasses(false);
        }

        $config->setMetadataCache($metadataCache);
        $config->setQueryCache($queryCache);
        $config->setHydrationCache($hydrateCache);
        $config->setResultCache($resultCache);

		$annotDriver = new AttributeDriver($c->get('doctrine.entity.path'));

        $config->setMetadataDriverImpl($annotDriver);
        $config->setProxyDir($c->get('app.cache.dir') . $c->get('doctrine.proxies.dir'));
        $config->setProxyNamespace($c->get('doctrine.proxies.namespace'));

        return $config;
    }
}
