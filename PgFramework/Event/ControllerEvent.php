<?php

namespace PgFramework\Event;

use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerEvent extends RequestEvent
{
    public const NAME = Events::CONTROLLER;

    private $controller;

    public function __construct(KernelInterface $kernel, $controller, ServerRequestInterface $request)
    {
        parent::__construct($kernel, $request);
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
