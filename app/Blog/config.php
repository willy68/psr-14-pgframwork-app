<?php

use App\Blog\BlogWidget;
use App\Blog\BlogAdminWidget;
use App\Blog\BlogTwigExtension;

return [
    'blog.prefix' => '/blog',
    'admin.widgets' => \DI\add([
       \DI\get(BlogAdminWidget::class)
    ]),
    'blog.widgets' => \DI\add([
        \DI\get(BlogWidget::class)
     ]),
     BlogTwigExtension::class => \DI\create()->constructor(\DI\get('blog.widgets')),
];
