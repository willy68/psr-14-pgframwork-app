<?php

namespace PgFramework\Session;

class PHPSession implements SessionInterface, \ArrayAccess, \Iterator, \Countable
{
    /**
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->ensureStarted();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     *
     */
    private function ensureStarted()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        $this->ensureStarted();
        return array_key_exists($offset, $_SESSION);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($_SESSION);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($_SESSION);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($_SESSION);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        next($_SESSION);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($_SESSION);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return key($_SESSION) !== null;
    }
}
