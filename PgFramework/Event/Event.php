<?php

namespace PgFramework\Event;

use League\Event\HasEventName;

class Event implements HasEventName
{
    public const NAME = '';

    public function eventName(): string
    {
        return static::NAME;
    }
}