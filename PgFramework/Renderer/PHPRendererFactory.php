<?php

declare(strict_types=1);

namespace PgFramework\Renderer;

use PgFramework\Renderer\PHPRenderer;
use Psr\Container\ContainerInterface;

class PHPRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return \PgFramework\Renderer\PHPRenderer
     */
    public function __invoke(ContainerInterface $container): PHPRenderer
    {
        $viewPath = $container->get('views.path');
        return new PHPRenderer($viewPath);
    }
}
