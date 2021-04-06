<?php

namespace Framework\Security\Firewall\Event;

use Framework\App;
use Framework\Event\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Security\Firewall\FirewallEvents;

class AuthorizationEvent extends RequestEvent
{
    public const NAME = FirewallEvents::AUTHORIZATION;

    public function __construct(App $app, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
    }
}
