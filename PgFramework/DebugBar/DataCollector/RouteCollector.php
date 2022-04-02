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

    public function __construct(RouteResult $routeResult, RouterInterface $router)
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
        $route = $this->routeResult->getMatchedRoute();

        if ($route) {
            $data = [
                'uri' => $this->router->generateUri($route->getName(), $this->routeResult->getMatchedParams()),
                'methods' => $route->getAllowedMethods(),
                'name' => $route->getName(),
                'callback' => $route->getCallback(),
            ];
            $text = $data['text'] = implode(', ', $data['methods']) . ' ' . $route->getPath();
        } else {
            $data = [
                'text' => 'route fail'
            ];
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->getVarDumper()->renderVar($value);
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
                "map" => "route",
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
