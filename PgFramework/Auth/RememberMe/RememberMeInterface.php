<?php

declare(strict_types=1);

namespace PgFramework\Auth\RememberMe;

use PgFramework\Auth\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RememberMeInterface
{
    /**
     * Crée un cookie d'authentification
     *
     * @param ResponseInterface $response
     * @param string $username
     * @param string $password
     * @param string $secret
     * @return ResponseInterface
     */
    public function onLogin(
        ResponseInterface $response,
        UserInterface $user
    ): ResponseInterface;

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return ServerRequestInterface with _user attribute or not
     */
    public function autoLogin(ServerRequestInterface $request): ServerRequestInterface;

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    /**
     * Renouvelle la date d'expiration du cookie dans la response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
