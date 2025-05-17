<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine;

use DI\Container;
use Doctrine\Persistence\Proxy;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\AbstractManagerRegistry;
use Psr\Container\NotFoundExceptionInterface;

class OrmManagerRegistry extends AbstractManagerRegistry
{
    protected ContainerInterface $container;

    /**
     *
     * @param string[] $connections
     * @param string[] $managers
     * @param string $defaultConnection
     * @param string $defaultManager
     * @param ContainerInterface $container
     */
    public function __construct(
        array $connections,
        array $managers,
        string $defaultConnection,
        string $defaultManager,
        ContainerInterface $container
    ) {
        parent::__construct('ORM', $connections, $managers, $defaultConnection, $defaultManager, Proxy::class);
        $this->setContainer($container);
    }

    /**
     * @inheritdoc
     */
    protected function getService($name)
    {
		try {
			return $this->container->get($name);
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
			return null;
		}
	}

    /**
     * @inheritdoc
     */
    protected function resetService($name): void
	{
        if ($this->container instanceof Container) {
            $this->container->set($name, null);
        }
    }

    public function setContainer(ContainerInterface $container = null): void
	{
        $this->container = $container;
    }


    /**
     * From Doctrine bundle Registry.php
     */
    public function getAliasNamespaces()
    {
        foreach (array_keys($this->getManagers()) as $name) {
            $objectManager = $this->getManager($name);

            if (!$objectManager instanceof EntityManagerInterface) {
                continue;
            }

			return $objectManager->getConfiguration()->getEntityNamespaces();
		}
		return [];
    }
}
