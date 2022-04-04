<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\Event;

use PgFramework\Event\RequestEvent;
use PgFramework\Kernel\KernelInterface;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Security\Firewall\FirewallEvents;

class AuthorizationEvent extends RequestEvent
{
    public const NAME = FirewallEvents::AUTHORIZATION;

    public function __construct(KernelInterface $kernel, ServerRequestInterface $request)
    {
        parent::__construct($kernel, $request);
    }
}
