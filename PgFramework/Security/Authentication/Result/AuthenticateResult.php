<?php

namespace PgFramework\Security\Authentication\Result;

use PgFramework\Auth\UserInterface;

class AuthenticateResult implements AuthenticateResultInterface
{
    protected $credentials;

    protected $user;

    public function __construct($credentials, UserInterface $user)
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
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * Set the value of credentials
     *
     * @return  self
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }
}
