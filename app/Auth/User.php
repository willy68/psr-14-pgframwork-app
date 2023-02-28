<?php

namespace App\Auth;

use DateTime;
use Exception;
use PgFramework\Auth\UserInterface;

class User implements UserInterface
{
    public int $id;

    public string $username;

    public string $email;

    public string $password;

    public array $roles;

    public ?string $passwordReset = null;

    public ?DateTime $passwordResetAt = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return  self
     */
    public function setId(int $id): static
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
     * @return string|null
     */
    public function getPasswordReset(): ?string
    {
        return $this->passwordReset;
    }

    /**
     * @param string $passwordReset
     */
    public function setPasswordReset(string $passwordReset)
    {
        $this->passwordReset = $passwordReset;
    }

    /**
     * @throws Exception
     */
    public function setPasswordResetAt($date)
    {
        if (is_string($date)) {
            $this->passwordResetAt = new DateTime($date);
        } else {
            $this->passwordResetAt = $date;
        }
    }

    /**
     * @return DateTime|null
     */
    public function getPasswordResetAt(): ?DateTime
    {
        return $this->passwordResetAt;
    }

    /**
     * @param string $email
     * @return  self
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $username
     * @return  self
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $password
     * @return  self
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param mixed $roles
     * @return  self
     */
    public function setRoles(mixed $roles): static
    {
        $this->roles = $roles;

        return $this;
    }
}
