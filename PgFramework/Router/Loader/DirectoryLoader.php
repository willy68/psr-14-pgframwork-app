<?php

declare(strict_types=1);

namespace PgFramework\Router\Loader;

use PgFramework\File\FileUtils;
use PgFramework\Router\Annotation\Route;

class DirectoryLoader extends FileLoader
{
    /**
     * Find all PHP files with @Route annotations
     *
     * @param string $dir
     * @return Route[]|null
     */
    public function load(string $dir): ?array
    {
        if (!is_dir($dir)) {
            return parent::load($dir);
        }

        $files = FileUtils::getFiles($dir);
        $routes = [];
        foreach ($files as $file) {
            $res = parent::load((string)$file);
            if ($res) {
                $routes[] = $res;
            }
        }

        if (empty($routes)) {
            return null;
        }

        return $routes;
    }
}
