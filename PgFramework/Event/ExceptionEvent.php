<?php

declare(strict_types=1);

namespace PgFramework\Event;

use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionEvent extends RequestEvent
{
    public const NAME = Events::EXCEPTION;

    private $exception;

    public function __construct(KernelInterface $kernel, ServerRequestInterface $request, \Throwable $e)
    {
        parent::__construct($kernel, $request);
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
