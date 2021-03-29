<?php

namespace Framework\Event;

use Framework\App;
use Psr\Http\Message\ServerRequestInterface;

class ControllerEvent extends RequestEvent
{
    public const NAME = Events::CONTROLLER;

    private $controller;

    public function __construct(App $app, callable $controller, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
        $this->controller = $controller;
    }

    public function getController(): callable
    {
        return $this->controller;
    }

    public function setController(callable $controller)
    {
        $this->controller = $controller;
    }
}
