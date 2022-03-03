<?php

namespace PgFramework\Security\Authentication;

use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;
use PgFramework\Security\Authentication\Result\AuthenticateResultInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationInterface
{
    /**
     * Authenticate the request
     *
     * Return AuthenticateResultInterface on authenticate success
     * or throw AuthenticationFailureException
     *
     * @param ServerRequestInterface $request
     * @return AuthenticateResultInterface
     * @throws AuthenticationFailureException
     */
    public function authenticate(ServerRequestInterface $request): AuthenticateResultInterface;

    /**
     * Return mixed credentials or null
     *
     * @param ServerRequestInterface $request
     * @return array|mixed|null
     */
    public function getCredentials(ServerRequestInterface $request);

    /**
     * Get user with $credentials
     *
     * @param mixed $credentials
     * @return UserInterface|mixed
     */
    public function getUser($credentials);

    /**
     * Action to do when success (generally redirect)
     *
     * If response is returned, the controller is not be executed
     * If null returned, the controller is executed
     *
     * @param ServerRequestInterface $request
     * @param mixed $user
     * @return ResponseInterface|null
     */
    public function onAuthenticateSuccess(ServerRequestInterface $request, $user): ?ResponseInterface;

    /**
     * Action to do when failed (generally redirect to login page)
     *
     * @param ServerRequestInterface $request
     * @param AuthenticationFailureException $e
     * @return ResponseInterface|null
     */
    public function onAuthenticateFailure(
        ServerRequestInterface $request,
        AuthenticationFailureException $e
    ): ?ResponseInterface;
}
