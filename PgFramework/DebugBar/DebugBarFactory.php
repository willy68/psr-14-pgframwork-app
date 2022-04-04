<?php

declare(strict_types=1);

namespace PgFramework\DebugBar;

use DebugBar\DebugBar;
use PgFramework\DebugBar\PgDebugBar;

class DebugBarFactory
{
    public function __invoke(): DebugBar
    {
        return new PgDebugBar();
    }
}
