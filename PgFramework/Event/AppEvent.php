<?php

namespace PgFramework\Event;

use PgFramework\ApplicationInterface;

class AppEvent extends StoppableEvent
{
    public const NAME = Events::REQUEST;

    private $app;

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    public function getApp(): ApplicationInterface
    {
        return $this->app;
    }
}
