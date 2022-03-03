<?php

namespace PgFramework\Event;

use PgFramework\App;
use PgFramework\ApplicationInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerParamsEvent extends ControllerEvent
{
    public const NAME = Events::PARAMETERS;

    private $params;

    public function __construct(
        ApplicationInterface $app,
        callable $controller,
        array $params,
        ServerRequestInterface $request
    ) {
        parent::__construct($app, $controller, $request);
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        $this->controller = $params;
    }
}
