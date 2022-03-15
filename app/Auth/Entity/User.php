<?php

namespace App\Auth\Entity;

use Doctrine\DBAL\Types\Types;
use PgFramework\Auth\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
#[Entity]
#[Table(name: 'users')]
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    public $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public $username;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public $email;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    #[Column(type: TYPES::STRING)]
    public $password;

    /**
     * @ORM\Column(type="json")
     * @var string[]
     */
    #[Column(type: TYPES::JSON)]
    public $roles = [];

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set the value of roles
     *
     * @return  self
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the value of roles
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
