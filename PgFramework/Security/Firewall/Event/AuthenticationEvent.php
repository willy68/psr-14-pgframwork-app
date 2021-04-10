<?php

namespace PgFramework\Security\Firewall\Event;

use PgFramework\App;
use PgFramework\Event\RequestEvent;
use PgFramework\Security\Firewall\FirewallEvents;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationEvent extends RequestEvent
{
    public const NAME = FirewallEvents::AUTHENTICATION;

    public function __construct(App $app, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
    }
}
