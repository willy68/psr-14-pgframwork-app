<?php

declare(strict_types=1);

namespace PgFramework\Session;

use ArrayAccess;
use Countable;
use Iterator;

use ReturnTypeWillChange;
use function count;

class PHPSession implements SessionInterface, ArrayAccess, Iterator, Countable
{
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public function unset(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    public function has(string $key): bool
    {
        $this->ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    private function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function offsetExists(mixed $offset)
    {
        $this->ensureStarted();
        return array_key_exists($offset, $_SESSION);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    #[ReturnTypeWillChange]
    public function offsetUnset(mixed $offset)
    {
        $this->unset($offset);
    }
    #[ReturnTypeWillChange]
    public function count()
    {
        $this->ensureStarted();
        return count($_SESSION);
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->ensureStarted();
        reset($_SESSION);
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        $this->ensureStarted();
        return current($_SESSION);
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        $this->ensureStarted();
        next($_SESSION);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        $this->ensureStarted();
        return key($_SESSION);
    }

    #[ReturnTypeWillChange]
    public function valid()
    {
        return $this->key() !== null;
    }
}
