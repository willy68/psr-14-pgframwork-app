<?php

namespace Framework\Event;

use Framework\App;

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
