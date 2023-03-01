<?php

namespace PgFramework\Database\Doctrine\Bridge;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;

class DebugMiddleware implements Middleware
{
    private DebugStack $debugStack;

    public function __construct(DebugStackInterface $debugStack)
    {
        $this->debugStack = $debugStack;
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return  new DebugDriver($driver, $this->debugStack);
    }
}
