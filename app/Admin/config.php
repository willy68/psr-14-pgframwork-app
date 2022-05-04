<?php

use App\Admin\AdminModule;
use App\Blog\BlogAdminWidget;
use App\Admin\AdminTwigExtension;

return [
    'admin.prefix' => '/admin',
    'admin.widgets' => \DI\add([
       \DI\get(BlogAdminWidget::class)
    ]),
    AdminTwigExtension::class => \DI\create()->constructor(\DI\get('admin.widgets')),
    AdminModule::class => \DI\autowire()
        ->constructorParameter('prefix', \DI\get('admin.prefix')),
];
