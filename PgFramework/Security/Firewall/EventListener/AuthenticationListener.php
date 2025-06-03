<?php

declare(strict_types=1);

namespace PgFramework\Security\Firewall\EventListener;

use PgFramework\Event\Events;
use League\Event\ListenerPriority;
use PgFramework\Event\RequestEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\EventDispatcher\EventSubscriberInterface;
use PgFramework\Security\Firewall\Event\LoginFailureEvent;
use PgFramework\Security\Firewall\Event\LoginSuccessEvent;
use PgFramework\Security\Authentication\AuthenticationInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * AuthenticationInterface Array
     *
     * @var AuthenticationInterface[]
     */
    private array $authenticators;

    private RememberMeInterface $rememberMe;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        array $authenticators,
        RememberMeInterface $rememberMe,
        EventDispatcherInterface $dispatcher
    ) {
        $this->authenticators = $authenticators;
        $this->rememberMe = $rememberMe;
        $this->dispatcher = $dispatcher;
    }

    public function onAuthentication(RequestEvent $event): void
    {
        $request = $event->getRequest();

        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->supports($request)) {
                try {
                    $result = $authenticator->authenticate($request);

                    /** @var LoginSuccessEvent $loginSuccessEvent*/
                    $loginSuccessEvent = $this->dispatcher->dispatch(new LoginSuccessEvent($result));
                    $result = $loginSuccessEvent->getResult();
                } catch (AuthenticationFailureException $e) {

                    /** @var LoginFailureEvent $loginFailureEvent*/
                    $loginFailureEvent = $this->dispatcher->dispatch(new LoginFailureEvent($e));

                    $response = $authenticator->onAuthenticateFailure(
                        $request,
                        $loginFailureEvent->getException()
                    );
                    if ($response instanceof ResponseInterface) {
                        $event->setResponse($response);
                        return;
                    }
                    continue;
                }

                $event->setRequest($request->withAttribute('auth.result', $result));

                $response = $authenticator->onAuthenticateSuccess($request, $result->getUser());

                if ($response instanceof ResponseInterface) {
                    if ($authenticator->supportsRememberMe($event->getRequest())) {
                        $response = $this->rememberMe->onLogin($response, $result->getUser());
                    }
                    $event->setResponse($response);
                    return;
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::REQUEST =>  ['onAuthentication', ListenerPriority::HIGH],
        ];
    }
}
