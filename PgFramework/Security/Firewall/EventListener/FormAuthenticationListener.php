<?php

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use Psr\Http\Message\ResponseInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\Security\Authentication\AuthenticationInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class FormAuthenticationListener implements EventSubscriberInterface
{
    private $authenticator;
    private $rememberMe;

    public function __construct(
        AuthenticationInterface $authenticator,
        RememberMeInterface $rememberMe)
    {
        $this->authenticator = $authenticator;
        $this->rememberMe = $rememberMe;
    }

    public function onAuthentication(RequestEvent $event)
    {
        $request = $event->getRequest();

        try {
            $user = $this->authenticator->authenticate($request);
        } catch (AuthenticationFailureException $e) {
            return $this->authenticator->onAuthenticateFailure($request);
        }

        $response = $this->authenticator->onAuthenticateSuccess($request, $user);

        if ($response instanceof ResponseInterface) {
            $event->setResponse($response);
        }

        $request->withAttribute('_user', $user);
        $event->setRequest($request);
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $params = $request->getParsedBody();
        if (($user = $request->getAttribute('_user')) && $params['rememberMe']) {
            $event->setResponse($this->rememberMe->onLogin($response, $user));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST => ['onAuthentication', ListenerPriority::HIGH],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW]
        ];
    }
}
