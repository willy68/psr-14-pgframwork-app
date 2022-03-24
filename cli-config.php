<?php

require 'app/Bootstrap/Bootstrap.php';

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager;
use Doctrine\Persistence\ManagerRegistry;

$migrationsConfig = new PhpFile('migrations.php');

$Manager = $app->getContainer()->get(ManagerRegistry::class);

return DependencyFactory::fromEntityManager(
    $migrationsConfig,
    ManagerRegistryEntityManager::withSimpleDefault($Manager, 'default')
);
