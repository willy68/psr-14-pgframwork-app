<?php

namespace Framework\Event;

use Framework\App;
use Psr\Http\Message\ServerRequestInterface;

class ControllerParamsEvent extends ControllerEvent
{
    public const NAME = Events::PARAMETERS;

    private $params;

    public function __construct(App $app, callable $controller, array $params, ServerRequestInterface $request)
    {
        parent::__construct($app, $controller, $request);
        $this->params = $params;
    }

    public function getParams(): iterable
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        $this->controller = $params;
    }
}
