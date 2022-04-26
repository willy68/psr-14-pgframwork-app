<?php

declare(strict_types=1);

namespace PgFramework;

abstract class AbstractApplication implements ApplicationInterface
{
    /**
     * Self static
     *
     * @var ApplicationInterface
     */
    protected static $app = null;

    /**
     * Get Self instance
     *
     * @return ApplicationInterface|null
     */
    public static function getApp(): ?ApplicationInterface
    {
        return static::$app;
    }
}
