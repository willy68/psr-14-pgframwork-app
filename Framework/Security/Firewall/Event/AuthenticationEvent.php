<?php

namespace Framework\Security\Firewall\Event;

use Framework\App;
use Framework\Event\RequestEvent;
use Framework\Security\Firewall\FirewallEvents;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationEvent extends RequestEvent
{
    public const NAME = FirewallEvents::AUTHENTICATION;

    public function __construct(App $app, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
    }
}
