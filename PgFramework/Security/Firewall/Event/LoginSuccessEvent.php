<?php

namespace PgFramework\Security\Firewall\Event;

use PgFramework\Event\Event;
use PgFramework\Security\Firewall\FirewallEvents;
use PgFramework\Security\Authentication\Result\AuthenticateResultInterface;

class LoginSuccessEvent extends Event
{
    public const NAME = FirewallEvents::LOGIN_SUCCESS;

    protected $result;

    public function __construct(AuthenticateResultInterface $result)
    {
        $this->result = $result;
    }

    /**
     * Get the value of result
     */ 
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set the value of result
     *
     * @return  self
     */ 
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
