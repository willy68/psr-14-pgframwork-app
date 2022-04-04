<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\Event;

use PgFramework\Event\Event;
use PgFramework\Security\Firewall\FirewallEvents;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class LoginFailureEvent extends Event
{
    public const NAME = FirewallEvents::LOGIN_FAILED;

    protected $exception;

    public function __construct(AuthenticationFailureException $e)
    {
        $this->exception = $e;
    }

    /**
     * Get the value of exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set the value of exception
     *
     * @return  self
     */
    public function setException(AuthenticationFailureException $exception)
    {
        $this->exception = $exception;

        return $this;
    }
}
