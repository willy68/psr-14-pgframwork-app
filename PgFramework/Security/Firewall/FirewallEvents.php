<?php

namespace PgFramework\Security\Firewall;

class FirewallEvents
{
    public const AUTHENTICATION = 'firewall.event.authentication';
    public const AUTHORIZATION = 'firewall.event.authorization';
    public const SECUTITY = 'firewall.event.security';
    public const LOGIN_SUCCESS = 'firewall.event.login.success';
    public const LOGIN_FAILED = 'firewall.event.login.failed';
}
