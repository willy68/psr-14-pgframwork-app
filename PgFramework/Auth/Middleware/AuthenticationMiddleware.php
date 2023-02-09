<?php

namespace PgFramework\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Security\Authentication\AuthenticationInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * AuthenticationInterface Array
     *
     * @var AuthenticationInterface[]
     */
    private $authenticators;

    private $rememberMe;

    public function __construct(
        array $authenticators,
        RememberMeInterface $rememberMe
    ) {
        $this->authenticators = $authenticators;
        $this->rememberMe = $rememberMe;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->authenticators as $authenticator) {
            if ($authenticator->supports($request)) {
                try {
                    $result = $authenticator->authenticate($request);

                    /** @var LoginSuccessEvent */
                    //$loginSuccessEvent = $this->dispatcher->dispatch(new LoginSuccessEvent($result));
                    //$result = $loginSuccessEvent->getResult();
                } catch (AuthenticationFailureException $e) {

                    /** @var LoginFailureEvent */
                    //$loginFailureEvent = $this->dispatcher->dispatch(new LoginFailureEvent($e));

                    $response = $authenticator->onAuthenticateFailure(
                        $request,
                        $e //$loginFailureEvent->getException()
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
