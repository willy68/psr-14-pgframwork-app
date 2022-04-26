<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\Event;

use PgFramework\Event\RequestEvent;
use PgFramework\Kernel\KernelInterface;
use PgFramework\Security\Firewall\FirewallEvents;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationEvent extends RequestEvent
{
    public const NAME = FirewallEvents::AUTHENTICATION;

    public function __construct(KernelInterface $kernel, ServerRequestInterface $request)
    {
        parent::__construct($kernel, $request);
    }
}
