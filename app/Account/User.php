<?php

namespace App\Account;

class User extends \App\Auth\User
{
    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param mixed $role
     */
    public function setRoles($roles)
    {
        $roles = json_decode($roles);
        $this->roles = $roles;
    }
}
