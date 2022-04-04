<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use PgFramework\Event\ResponseEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\Security\Firewall\Event\LoginFailureEvent;
use PgFramework\Security\Firewall\Event\LoginSuccessEvent;
use PgFramework\Security\Authentication\AuthenticationInterface;
use PgFramework\Security\Authentication\Result\AuthenticateResultInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class FormAuthenticationListener implements EventSubscriberInterface
{
    private $authenticator;
    private $rememberMe;
    private $dispatcher;

    public function __construct(
        AuthenticationInterface $authenticator,
        RememberMeInterface $rememberMe,
        EventDispatcherInterface $dispatcher
    ) {
        $this->authenticator = $authenticator;
        $this->rememberMe = $rememberMe;
        $this->dispatcher = $dispatcher;
    }

    public function onAuthentication(RequestEvent $event)
    {
        $request = $event->getRequest();

        try {
            $result = $this->authenticator->authenticate($request);

            /** @var LoginSuccessEvent */
            $loginSuccessEvent = $this->dispatcher->dispatch(new LoginSuccessEvent($result));
            $result = $loginSuccessEvent->getResult();
        } catch (AuthenticationFailureException $e) {

            /** @var LoginFailureEvent */
            $loginFailureEvent = $this->dispatcher->dispatch(new LoginFailureEvent($e));

            $response = $this->authenticator->onAuthenticateFailure(
                $request,
                $loginFailureEvent->getException()
            );
            if ($response instanceof ResponseInterface) {
                $event->setResponse($response);
            }
            return;
        }

        $response = $this->authenticator->onAuthenticateSuccess($request, $result->getUser());

        if ($response instanceof ResponseInterface) {
            $event->setResponse($response);
        }

        $event->setRequest($request->withAttribute('auth.result', $result));
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        /** @var AuthenticateResultInterface $result */
        if (($result = $request->getAttribute('auth.result'))) {
            $credentials = $result->getCredentials();
            if (isset($credentials['rememberMe'])) {
                $event->setResponse($this->rememberMe->onLogin($response, $result->getUser()));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::REQUEST =>  ['onAuthentication', ListenerPriority::HIGH],
            Events::RESPONSE => ['onResponse', ListenerPriority::LOW]
        ];
    }
}
