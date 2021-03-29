<?php

use Psr\Container\ContainerInterface;
use Framework\Environnement\Environnement;

return [
    'database.sgdb' => Environnement::getEnv('DATABASE_SGDB', 'mysql'),
    'database.host' => Environnement::getEnv('DATABASE_HOST', 'localhost'),
    'database.user' => Environnement::getEnv('DATABASE_USER', 'root'),
    'database.password' => Environnement::getEnv('DATABASE_PASSWORD', 'root'),
    'database.name' => Environnement::getEnv('DATABASE_NAME', 'my_database'),
    'ActiveRecord.connections' => function (ContainerInterface $c): array {
        return [
            'development' => $c->get('database.sgdb') . "://" .
                $c->get('database.user') . ":" .
                $c->get('database.password') . "@" .
                $c->get('database.host') . "/" .
                $c->get('database.name') . "?charset=utf8"
        ];
    }
];
