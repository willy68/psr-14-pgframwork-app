<?php

declare(strict_types=1);

namespace PgFramework\Router\Loader;

use Mezzio\Router\Route;
use PgFramework\Parser\PhpTokenParser;

class FileLoader extends RouteLoader
{
    /**
     * Parse annotations @Route and add routes to the router
     *
     * @param string $file
     * @return Route[]|null
     */
    public function load(string $file): ?array
    {
        if (!is_file($file)) {
            return parent::load($file);
        }

        $class = PhpTokenParser::findClass($file);
        if (false !== $class) {
            return parent::load($class);
        }
        return null;
    }
}
