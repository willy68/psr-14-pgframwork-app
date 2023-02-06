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
}
