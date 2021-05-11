<?php

namespace PgFramework\Security\Authentication\Result;

use PgFramework\Auth\User;

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
     * @return User
     */
    public function getUser(): User;

    /**
     * set the user
     *
     * @param User $user
     * @return self
     */
    public function setUser(User $user);
}
