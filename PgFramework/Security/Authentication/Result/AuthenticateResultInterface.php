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
    public function getCredentials();

    /**
     * Set the credentials
     *
     * @param mixed $credentials
     * @return self
     */
    public function setCredentials($credentials);

    /**
     * Get authenticate user
     *
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * set the user
     *
     * @param UserInterface $user
     * @return self
     */
    public function setUser(UserInterface $user);
}
