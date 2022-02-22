<?php

namespace App\PgFramework\Database\Doctrine;

use Psr\Container\ContainerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\AbstractManagerRegistry;
use Doctrine\ORM\Exception\UnknownEntityNamespace;

class ManagerRegistry extends AbstractManagerRegistry
{
     /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @inheritdoc
     */
    protected function getService($name)
    {
        return $this->container->get($name);
    }

    /**
     * @inheritdoc
     */
    protected function resetService($name)
    {
        if ($this->container instanceof \DI\Container) {
            $this->container->set($name, null);
        }
    }

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    /**
     * @inheritdoc
     */
    public function getAliasNamespace($alias)
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }

        throw UnknownEntityNamespace::fromNamespaceAlias($alias);
    }


}