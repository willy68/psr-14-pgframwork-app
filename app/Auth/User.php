<?php

namespace App\Auth;

use PgFramework\Auth\UserInterface;

class User implements UserInterface
{
    public $id;

    public $username;

    public $email;

    public $password;

    public $roles;

    public $passwordReset;

    public $passwordResetAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
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

    /**
     * @return mixed
     */
    public function getPasswordReset()
    {
        return $this->passwordReset;
    }

    /**
     * @param mixed $passwordReset
     */
    public function setPasswordReset($passwordReset)
    {
        $this->passwordReset = $passwordReset;
    }

    public function setPasswordResetAt($date)
    {
        if (is_string($date)) {
            $this->passwordResetAt = new \DateTime($date);
        } else {
            $this->passwordResetAt = $date;
        }
    }

    /**
     * @return mixed
     */
    public function getPasswordResetAt(): ?\DateTime
    {
        return $this->passwordResetAt;
    }

    /**
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return  self
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }
}
