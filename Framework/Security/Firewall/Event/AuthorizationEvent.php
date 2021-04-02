<?php

namespace Framework\Security\Firewall\Event;

use Framework\App;
use Framework\Event\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationEvent extends RequestEvent
{

    public function __construct(App $app, ServerRequestInterface $request)
    {
        parent::__construct($app, $request);
    }
}
