<?php

declare(strict_types=1);

namespace PgFramework\Renderer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PHPRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return PHPRenderer
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PHPRenderer
    {
        $viewPath = $container->get('views.path');
        return new PHPRenderer($viewPath);
    }
}
