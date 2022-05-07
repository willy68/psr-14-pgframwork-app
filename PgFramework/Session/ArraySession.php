<?php

declare(strict_types=1);

namespace PgFramework\Session;

use Mezzio\Session\SessionInterface;

class ArraySession implements SessionInterface
{
    private $session = [];

    private $changed = false;

    private $regenerated = false;

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->session)) {
            return $this->session[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->session[$key] = $value;
        $this->changed = true;
    }

    /**
     * @param string $key
     * @return void
     */
    public function unset(string $key): void
    {
        unset($this->session[$key]);
        $this->changed = true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->session);
    }

    public function clear(): void
    {
        $this->session = [];
        $this->changed = true;
    }

    public function toArray(): array
    {
        return $this->session;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }

    public function regenerate(): SessionInterface
    {
        $session = new self();
        $session->session = $this->session;
        $this->regenerated = true;
        return $session;
    }

    public function isRegenerated(): bool
    {
        return $this->regenerated;
    }
}
