<?php

declare(strict_types=1);

namespace PgFramework\Security\Authentication\Result;

use PgFramework\Auth\UserInterface;

class AuthenticateResult implements AuthenticateResultInterface
{
    protected mixed $credentials;

    protected UserInterface $user;

    public function __construct($credentials, UserInterface $user)
    {
        $this->credentials = $credentials;
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getCredentials(): mixed
    {
        return $this->credentials;
    }

    /**
     * @inheritdoc
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * Set the value of credentials
     *
     * @param mixed $credentials
     * @return  self
     */
    public function setCredentials(mixed $credentials): static
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Set the value of user
     *
     * @param UserInterface $user
     * @return  self
     */
    public function setUser(UserInterface $user): static
    {
        $this->user = $user;

        return $this;
    }
}
