<?php

namespace App\Account;

class User extends \App\Auth\User
{
    public string $firstname;

    public string $lastname;

    public $roles = [];

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $roles = json_decode($roles);
        $this->roles = $roles;
    }
}
