<?php

declare(strict_types=1);

namespace PgFramework\Event;

class Events
{
    public const REQUEST = 'event.request';
    public const CONTROLLER = 'event.controller';
    public const PARAMETERS = 'event.parameters';
    public const VIEW = 'event.view';
    public const RESPONSE = 'event.response';
    public const EXCEPTION = 'event.exception';
}
