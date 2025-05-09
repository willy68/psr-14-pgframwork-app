<?php

use App\Blog\BlogTwigExtension;
use App\Blog\BlogWidget;

use function DI\add;
use function DI\create;
use function DI\get;

return [
    'blog.prefix' => '/blog',
    'blog.widgets' => add([
        get(BlogWidget::class)
    ]),
    BlogTwigExtension::class => create()->constructor(get('blog.widgets')),
];
