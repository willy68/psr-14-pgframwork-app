<?php

namespace PgFramework\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use PgFramework\Router\RoutesMapInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Security\Firewall\Event\LoginFailureEvent;
use PgFramework\Security\Firewall\Event\LoginSuccessEvent;
use PgFramework\Security\Authentication\AuthenticationInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class AuthenticationMiddleware implements MiddlewareInterface
{

    /**
     * @var AuthenticationInterface[]
     */
    private array $authenticators;

    private RememberMeInterface $rememberMe;

    private EventDispatcherInterface|EventDispatcher $dispatcher;

    private RoutesMapInterface $map;

    public function __construct(
        array $authenticators,
        RememberMeInterface $rememberMe,
        EventDispatcherInterface $dispatcher,
        RoutesMapInterface $map
    ) {
        $this->authenticators = $authenticators;
        $this->rememberMe = $rememberMe;
        $this->dispatcher = $dispatcher;
        $this->map = $map;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$listeners] = $this->map->getListeners($request);
        foreach ($listeners as $listener) {
            $this->dispatcher->addSubscriber($listener);
        }
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
                        return $response;
                    }
                    continue;
                }

                $request = $request->withAttribute('auth.result', $result);

                $response = $authenticator->onAuthenticateSuccess($request, $result->getUser());

                if ($response instanceof ResponseInterface) {
                    if ($authenticator->supportsRememberMe($request)) {
                        $response = $this->rememberMe->onLogin($response, $result->getUser());
                    }
                    return $response;
                }
            }
        }
        return $handler->handle($request);
    }
}
