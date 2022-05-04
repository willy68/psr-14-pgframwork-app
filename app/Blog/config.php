<?php

use App\Blog\BlogWidget;
use App\Blog\BlogTwigExtension;

return [
    'blog.prefix' => '/blog',
    'blog.widgets' => \DI\add([
        \DI\get(BlogWidget::class)
     ]),
     BlogTwigExtension::class => \DI\create()->constructor(\DI\get('blog.widgets')),
];
