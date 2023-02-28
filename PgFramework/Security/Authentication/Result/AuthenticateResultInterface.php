<?php

declare(strict_types=1);

namespace PgFramework\Security\Authentication\Result;

use PgFramework\Auth\UserInterface;

interface AuthenticateResultInterface
{
    /**
     * Get credentials used to login
     *
     * @return mixed
     */
    public function getCredentials(): mixed;

    /**
     * Set the credentials
     *
     * @param mixed $credentials
     * @return self
     */
    public function setCredentials(mixed $credentials): static;

    /**
     * Get authenticate user
     *
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * Set the user
     *
     * @param UserInterface $user
     * @return self
     */
    public function setUser(UserInterface $user): static;
}
