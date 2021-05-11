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
     * Get authenticate user
     *
     * @return User
     */
    public function getUser(): User;
}
