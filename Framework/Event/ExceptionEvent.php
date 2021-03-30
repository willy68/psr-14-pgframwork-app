<?php

namespace Framework\Event;

use Framework\App;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionEvent extends RequestEvent
{
    private $exception;

    public function __construct(App $app, ServerRequestInterface $request, \Throwable $e)
    {
        parent::__construct($app, $request);
        $this->exception = $e;
    }

    /**
     * Get the value of exception
     */ 
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * Set the value of exception
     *
     * @return  self
     */ 
    public function setException(\Throwable $exception)
    {
        $this->exception = $exception;
        return $this;
    }
}
