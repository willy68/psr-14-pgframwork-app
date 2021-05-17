<?php

namespace App\Auth;

use PgFramework\Auth\UserInterface;

class User implements UserInterface
{
    public $id;

    public $username;

    public $email;

    public $password;

    private $roles = [];

    /**
     *
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

        /**
     * Undocumented function
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        if (is_string($this->roles)) {
            return json_decode($this->roles);
        }
        return $this->roles;
    }

    /**
     * Get the value of email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
