<?php

namespace PgFramework\DebugBar\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\DataCollector;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;

class RouteCollector extends DataCollector implements Renderable, AssetProvider
{
    protected $routeResult;

    protected $router;

    public function __construct(RouterInterface $router, ?RouteResult $routeResult = null)
    {
        $this->routeResult = $routeResult;
        $this->router = $router;
    }

    public function getName()
    {
        return 'route';
    }

    public function collect()
    {
        if (null === $this->routeResult) {
            return $data = [
                'text' => 'route fail'
            ];
        }

        $route = $this->routeResult->getMatchedRoute();
        if ($route) {
            $data['data'] = [
                'uri' => $this->router->generateUri($route->getName(), $this->routeResult->getMatchedParams()),
                'methods' => $route->getAllowedMethods(),
                'name' => $route->getName(),
                'callback' => $route->getCallback(),
            ];
            $methods = $data['data']['methods'] ?? [];
            $text = implode(', ', $methods) . ' ' . $route->getPath();
        }

        foreach ($data['data'] as $key => $value) {
            $data['data'][$key] = $this->getVarDumper()->renderVar($value);
        }
        $data['text'] = $text;

        return $data;
    }

    public function getWidgets()
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
    public function getAssets()
    {
        return $this->getVarDumper()->getAssets();
    }
}
