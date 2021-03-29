<?php

use App\Admin\AdminModule;
use App\Admin\AdminTwigExtension;
use App\Admin\DashboardAction;

return [
  'admin.prefix' => '/admin',
  'admin.widgets' => [],
  AdminTwigExtension::class => \DI\create()->constructor(\DI\get('admin.widgets')),
  AdminModule::class => \DI\autowire()
    ->constructorParameter('prefix', \DI\get('admin.prefix')),
  DashboardAction::class => \DI\autowire()
    ->constructorParameter('widgets', \DI\get('admin.widgets'))
];
