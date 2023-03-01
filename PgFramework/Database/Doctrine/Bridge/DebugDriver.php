<?php

declare(strict_types=1);

namespace PgFramework\Database\Doctrine\Bridge;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

class DebugDriver extends AbstractDriverMiddleware
{
    private DebugStack $debugStack;
    private string $connectionName;

    public function __construct(DriverInterface $wrappedDriver, DebugStackInterface $debugStack, string $connectionName)
    {
        parent::__construct($wrappedDriver);
        $this->debugStack = $debugStack;
        $this->connectionName = $connectionName;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(
        array $params
    ): DebugConnection|DriverInterface\Connection {
        return new DebugConnection(
            parent::connect($params),
            $this->debugStack,
            $this->connectionName
        );
    }

    /**
     * @param array<string,mixed> $params Connection parameters
     *
     * @return array<string,mixed>
     */
    private function maskPassword(
        array $params
    ): array {
        if (isset($params['password'])) {
            $params['password'] = '<redacted>';
        }

        if (isset($params['url'])) {
            $params['url'] = '<redacted>';
        }

        return $params;
    }
}
