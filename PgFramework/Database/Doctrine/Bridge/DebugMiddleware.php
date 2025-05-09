<?php

namespace PgFramework\Database\Doctrine\Bridge;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;

class DebugMiddleware implements Middleware
{
    private DebugStack $debugStack;
    private string $connectionName;

    public function __construct(DebugStackInterface $debugStack, string $connectionName = 'default')
    {
        $this->debugStack = $debugStack;
        $this->connectionName = $connectionName;
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return  new DebugDriver($driver, $this->debugStack, $this->connectionName);
    }
}
