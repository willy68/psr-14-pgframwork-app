<?php

namespace App\Auth\Models;

use ActiveRecord\Model;
use Framework\Auth\User as AuthUser;

class User extends Model implements AuthUser
{
    public static $connection = 'blog';

    public static $table_name = 'users';

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
