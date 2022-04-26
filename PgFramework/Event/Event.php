<?php

declare(strict_types=1);

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
