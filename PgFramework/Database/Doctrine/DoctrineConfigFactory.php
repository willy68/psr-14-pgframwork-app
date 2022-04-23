<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Configuration;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class DoctrineConfigFactory
{
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
            $annotCache = new ArrayAdapter();
            $config->setAutoGenerateProxyClasses(true);
        } else {
            $queryCache = new PhpFilesAdapter('doctrine_queries', 0, $c->get('app.cache.dir') . '/orm');
            $metadataCache = new PhpFilesAdapter('doctrine_metadata', 0, $c->get('app.cache.dir') . '/orm');
            $hydrateCache = new PhpFilesAdapter('doctrine.hydrate', 0, $c->get('app.cache.dir') . '/orm');
            $resultCache = new PhpFilesAdapter('doctrine.result', 0, $c->get('app.cache.dir') . '/orm');
            $annotCache = new PhpFilesAdapter('doctrine.annotaions', 0, $c->get('app.cache.dir') . '/orm');
            $config->setAutoGenerateProxyClasses(false);
        }

        $config->setMetadataCache($metadataCache);
        $config->setQueryCache($queryCache);
        $config->setHydrationCache($hydrateCache);
        $config->setResultCache($resultCache);

        if (PHP_VERSION_ID >= 80000) {
            $annotDriver = new AttributeDriver($c->get('doctrine.entity.path'));
        } else {
            $annotDriver = ORMSetup::createDefaultAnnotationDriver(
                $c->get('doctrine.entity.path'),
                $annotCache
            );
        }

        $config->setMetadataDriverImpl($annotDriver);
        $config->setProxyDir($c->get('doctrine.proxies.dir'));
        $config->setProxyNamespace($c->get('doctrine.proxies.namespace'));

        return $config;
    }
}
