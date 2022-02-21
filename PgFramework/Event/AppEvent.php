<?php

namespace PgFramework\Event;

class AppEvent extends StoppableEvent
{
    public const NAME = Events::REQUEST;

    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }
}
