<?php

namespace PgFramework\Security\Firewall\Event;

use PgFramework\App;
use PgFramework\Event\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Security\Firewall\FirewallEvents;

class AuthorizationEvent extends RequestEvent
{
    public const NAME = FirewallEvents::AUTHORIZATION;

    public function __construct(App $app, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
    }
}
