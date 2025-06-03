<?php

declare(strict_types=1);

namespace PgFramework\Session;

use Mezzio\Session\SessionInterface;

class ArraySession implements SessionInterface
{
    private array $session = [];
    private bool $changed = false;
    private bool $regenerated = false;

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null): mixed
    {
        if (array_key_exists($name, $this->session)) {
            return $this->session[$name];
        }
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, $value): void
    {
        $this->session[$name] = $value;
        $this->changed = true;
    }

    /**
     * @param string $name
     * @return void
     */
    public function unset(string $name): void
    {
        unset($this->session[$name]);
        $this->changed = true;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->session);
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
