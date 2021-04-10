<?php

namespace PgFramework\Event;

use PgFramework\App;

class AppEvent extends StoppableEvent
{
    public const NAME = Events::REQUEST;

    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getApp(): App
    {
        return $this->app;
    }
}
