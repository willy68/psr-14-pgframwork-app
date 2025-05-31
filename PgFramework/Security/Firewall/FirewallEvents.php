<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall;

class FirewallEvents
{
    public const AUTHENTICATION = 'firewall.event.authentication';
    public const AUTHORIZATION = 'firewall.event.authorization';
    public const SECURITY = 'firewall.event.security';
    public const LOGIN_SUCCESS = 'firewall.event.login.success';
    public const LOGIN_FAILED = 'firewall.event.login.failed';
}
