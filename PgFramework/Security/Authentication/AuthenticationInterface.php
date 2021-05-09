<?php

namespace PgFramework\Security\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationInterface
{
    public function authenticate(ServerRequestInterface $request);

    public function getCredentials(ServerRequestInterface $request): ?array;

    public function getUser($credentiels);

    public function onAuthenticateSuccess(ServerRequestInterface $request, $user): ?ResponseInterface;

    public function onAuthenticateFailure(ServerRequestInterface $request): ?ResponseInterface;
}
