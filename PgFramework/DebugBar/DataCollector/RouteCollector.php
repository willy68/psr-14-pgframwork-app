<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\DataCollector;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PgRouter\Route;

class RouteCollector extends DataCollector implements Renderable, AssetProvider
{
    protected $routeResult;

    protected $router;

    public function __construct(RouterInterface $router, ?RouteResult $routeResult = null)
    {
        $this->routeResult = $routeResult;
        $this->router = $router;
    }

    public function getName(): string
    {
        return 'route';
    }

    public function collect(): array
    {
        $data = [
            'text' => 'route fail',
            'data' => []
        ];

        if (null === $this->routeResult) {
            return $data;
        }

        /** @var false|Route $route */
        $route = $this->routeResult->getMatchedRoute();
        if ($route) {
            $data['data'] = [
                'uri' => $this->router->generateUri($route->getName(), $this->routeResult->getMatchedParams()),
                'methods' => $route->getAllowedMethods(),
                'name' => $route->getName(),
                'callback' => $route->getCallback(),
                'params' => $this->routeResult->getMatchedParams(),
                'middleware' => $route->getMiddlewareStack(),
            ];
            $methods = $data['data']['methods'] ?? [];
            $data['text'] = implode(', ', $methods) . ' ' . $route->getPath();
        }
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key] = $this->getVarDumper()->renderVar($value);
        }

        return $data;
    }

    public function getWidgets(): array
    {
        return [
            "route" => [
                "icon" => "share",
                "widget" => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                "map" => "route.data",
                "default" => "{}"
            ],
            'currentroute' => [
                "icon" => "share",
                "tooltip" => "Route",
                "map" => "route.text",
                "default" => ""
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->getVarDumper()->getAssets();
    }
}
