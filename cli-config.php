<?php

require 'app/Bootstrap/Bootstrap.php';

use Doctrine\ORM\EntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

$migrationsConfig = new PhpFile('migrations.php');

$entityManager = $app->getContainer()->get(EntityManager::class);

$depencyFactory = DependencyFactory::fromEntityManager($migrationsConfig, new ExistingEntityManager($entityManager));

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
