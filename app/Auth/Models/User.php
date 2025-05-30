<?php

namespace App\Auth\Models;

use ActiveRecord\Model;
use PgFramework\Auth\UserInterface;

class User extends Model implements UserInterface
{
    public static $connection = 'blog';

    public static $table_name = 'users';

    public static array $before_save = array('encodeRoles'); # new OR updated records

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        if (is_string($this->roles)) {
            return json_decode($this->roles);
        }
        return $this->roles;
    }

    public function encodeRoles()
    {
        if (is_array($this->roles)) {
            $this->roles = json_encode($this->roles);
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
