<?php

declare(strict_types=1);

namespace PgFramework\Renderer;

interface RendererInterface
{
    /**
     * @param string $namespace
     * @param string|null $path
     * @return void
     */
    public function addPath(string $namespace, string $path = null): void;

    /**
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string;

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addGlobal(string $key, mixed $value): void;
}
