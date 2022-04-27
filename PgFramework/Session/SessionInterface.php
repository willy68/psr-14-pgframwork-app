<?php

declare(strict_types=1);

namespace PgFramework\Session;

interface SessionInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * @param string $key
     * @return void
     */
    public function unset(string $key): void;

    /**
     * Check session has key
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool;
}
