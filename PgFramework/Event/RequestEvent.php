<?php

namespace PgFramework\Event;

use PgFramework\ApplicationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestEvent extends AppEvent
{
    private ServerRequestInterface $request;

    protected $response;

    public function __construct(ApplicationInterface $app, ServerRequestInterface $request)
    {
        parent::__construct($app);
        $this->request = $request;
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
        $this->stopPropagation();
    }

    public function hasResponse(): bool
    {
        return null !== $this->response;
    }
}
