<?php

namespace Framework\Event;

use Framework\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResponseEvent extends RequestEvent
{
    public const NAME = Events::RESPONSE;

    private ResponseInterface $response;

    public function __construct(App $app, ServerRequestInterface $request, ResponseInterface $response)
    {
        parent::__construct($app, $request);
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }
}
