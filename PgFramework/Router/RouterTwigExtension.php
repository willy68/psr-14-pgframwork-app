<?php

declare(strict_types=1);

namespace PgFramework\Router;

use Exception;
use Pg\Router\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function str_contains;

class RouterTwigExtension extends AbstractExtension
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'pathFor']),
            new TwigFunction('is_subpath', [$this, 'isSubPath'])
        ];
    }

	/**
	 * Génère un lien html
	 *
	 * @param string $path
	 * @param array $params
	 * @return string
	 * @throws Exception
	 */
    public function pathFor(string $path, array $params = []): string
    {
        return $this->router->generateUri($path, $params);
    }

	/**
	 * Détermine si un chemin est un sous chemin
	 *
	 * @param string $path
	 * @return bool
	 * @throws Exception
	 */
    public function isSubPath(string $path): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $expected = $this->router->generateUri($path);
        return str_contains($uri, $expected);
    }
}
