<?php

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Psr\Container\ContainerInterface;
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
            $config->setAutoGenerateProxyClasses(true);
        } else {
            $queryCache = new PhpFilesAdapter('doctrine_queries');
            $metadataCache = new PhpFilesAdapter('doctrine_metadata');
            $config->setAutoGenerateProxyClasses(false);
        }

        $config->setMetadataCache($metadataCache);
        $config->setQueryCache($queryCache);

        if (PHP_VERSION_ID >= 80000) {
            $annotDriver = new AttributeDriver($c->get('doctrine.entity.path'));
        } else {
            $annotDriver = $config->newDefaultAnnotationDriver(
                $c->get('doctrine.entity.path'),
                false
            );
        }

        $config->setMetadataDriverImpl($annotDriver);
        $config->setProxyDir($c->get('doctrine.proxies.dir'));
        $config->setProxyNamespace($c->get('doctrine.proxies.namespace'));

        return $config;
    }
}