<?php

use App\Admin\AdminModule;
use App\Admin\BlogAdminWidget;
use App\Admin\AdminTwigExtension;

use function DI\add;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    'admin.prefix' => '/admin',
    'admin.widgets' => add([
       get(BlogAdminWidget::class)
    ]),
    AdminTwigExtension::class => create()->constructor(get('admin.widgets')),
    AdminModule::class => autowire()
        ->constructorParameter('prefix', get('admin.prefix')),
];
