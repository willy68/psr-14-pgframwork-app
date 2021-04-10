<?php

namespace PgFramework\Event;

use PgFramework\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResponseEvent extends AppEvent
{
    public const NAME = Events::RESPONSE;

    private $request;

    private $response;

    public function __construct(App $app, ServerRequestInterface $request, ResponseInterface $response)
    {
        parent::__construct($app);
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function hasResponse(): bool
    {
        return null !== $this->response;
    }
}
