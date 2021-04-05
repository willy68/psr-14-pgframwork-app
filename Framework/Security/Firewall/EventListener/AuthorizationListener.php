<?php

namespace Framework\Security\Firewall\EventListener;

use Framework\Auth;
use Framework\Router\RequestMatcher;
use Framework\Security\Authorization\VoterManagerInterface;
use Framework\Security\Firewall\Event\AuthorizationEvent;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationListener
{
    protected $auth;

    protected $voterManager;

    public function __construct(Auth $auth, VoterManagerInterface $voterManager)
    {
        $this->auth = $auth;
        $this->voterManager = $voterManager;
    }

    public function support(ServerRequestInterface $request): bool
    {
        $requestMatcher = new RequestMatcher();


        return false;
    }

    public function onAuthorization(AuthorizationEvent $event)
    {

        $request = $event->getRequest();

        if (!$this->support($request)) {
            return;
        }

        $attributes = $request->getAttribute('_access_control_attributes');
        $request = $request->withoutAttribute('_access_control_attributes');

        if (!$attributes || $event instanceof AuthorizationEvent) {
            return;
        }

        if ($event instanceof AuthorizationEvent && null === $user = $this->auth->getUser()) {
            throw new \Exception('A Token was not found in the TokenStorage.');
        }

        if (!$this->voterManager->decide($this->auth, $attributes, $request, true)) {
            $exception = new \Exception();
            //$exception->setAttributes($attributes);
            //$exception->setSubject($request);

            throw $exception;
        }
        $event->setRequest($request);  
    }
}
