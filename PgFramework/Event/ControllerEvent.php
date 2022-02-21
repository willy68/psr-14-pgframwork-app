<?php

namespace PgFramework\Event;

use PgFramework\ApplicationInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerEvent extends RequestEvent
{
    public const NAME = Events::CONTROLLER;

    private $controller;

    public function __construct(ApplicationInterface $app, $controller, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }
}
