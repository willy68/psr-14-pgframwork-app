<?php

namespace PgFramework\Database\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class EntityManagerFactory
{
    public function __invoke(ContainerInterface $c, string $connectionEntry): EntityManager
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

        $driverChain = new MappingDriverChain();
        $annotDriver = $config->newDefaultAnnotationDriver(
            $c->get('doctrine.entity.path'),
            false
        );
        $attributeDriver = new AttributeDriver($c->get('doctrine.entity.path'));

        foreach ($c->get('doctrine.entity.namespace') as $namespace) {
            $driverChain->addDriver($attributeDriver, $namespace);
            //$driverChain->addDriver($annotDriver, $namespace);
        }

        $driverChain->setDefaultDriver($attributeDriver);

        $config->setMetadataDriverImpl($driverChain);
        $config->setQueryCache($queryCache);
        $config->setProxyDir($c->get('doctrine.proxies.dir'));
        $config->setProxyNamespace($c->get('doctrine.proxies.namespace'));

        return EntityManager::create($c->get($connectionEntry), $config);
    }
}
