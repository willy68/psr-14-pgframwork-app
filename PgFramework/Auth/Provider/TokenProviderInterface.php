<?php

declare(strict_types=1);

namespace PgFramework\Auth\Provider;

use PgFramework\Auth\TokenInterface;

interface TokenProviderInterface
{
    /**
     * get cookie token from database or what else
     *
     * @param mixed $credential
     * @return \PgFramework\Auth\TokenInterface|null
     */
    public function getTokenBySeries($series): ?TokenInterface;

    /**
     * get cookie token from database or what else
     *
     * @param mixed $credential
     * @return \PgFramework\Auth\TokenInterface|null
     */
    public function getTokenByCredential($credential): ?TokenInterface;

    /**
     * Sauvegarde le token (database, cookie, les deux ou autre)
     *
     * @param array $token
     * @return TokenInterface|null
     */
    public function saveToken(array $token): ?TokenInterface;

    /**
     * Mise à jour du token en database
     *
     * @param array $token
     * @param mixed $id
     * @return TokenInterface|null
     */
    public function updateToken(array $token, $id): ?TokenInterface;

    /**
     * Detruit le token inutile
     *
     * @param int $id
     * @return void
     */
    public function deleteToken(int $id);
}
