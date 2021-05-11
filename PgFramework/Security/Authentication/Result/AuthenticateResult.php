<?php

namespace PgFramework\Security\Authentication\Result;

use PgFramework\Auth\User;

class AuthenticateResult implements AuthenticateResultInterface
{

    protected $credentials;

    protected $user;

    public function __construct($credentials, User $user)
    {
        $this->credentials = $credentials;
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @inheritdoc
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
