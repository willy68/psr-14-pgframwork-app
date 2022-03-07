<?php

namespace PgFramework\Event;

use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ServerRequestInterface;

class ViewEvent extends RequestEvent
{
    public const NAME = Events::VIEW;

    private $result;

    public function __construct(KernelInterface $kernel, ServerRequestInterface $request, $result)
    {
        parent::__construct($kernel, $request);
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
