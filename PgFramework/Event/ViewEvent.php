<?php

namespace PgFramework\Event;

use PgFramework\ApplicationInterface;
use Psr\Http\Message\ServerRequestInterface;

class ViewEvent extends RequestEvent
{
    public const NAME = Events::VIEW;

    private $result;

    public function __construct(ApplicationInterface $app, ServerRequestInterface $request, $result)
    {
        parent::__construct($app, $request);
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

}
